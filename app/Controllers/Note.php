<?php

namespace App\Controllers;

use CodeIgniter\Database\RawSql;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;
use App\Libraries\Markdown;
use App\Models\NoteModel;
use App\Models\NoteRevisionModel;

class Note extends BaseController
{
    /**
     * Display the editor for a new note.
     */
    public function new()
    {
        helper('cookie');
        if (get_cookie('noteskey') === null) {
            return redirect()->to('/admin/notes/key');
        }

        $data['datatables'] = false;
        $data['js']         = ['vendor/marked.min', 'note-editor', 'markdown-expanders'];
        $data['css']        = ['note-editor'];
        $data['title']      = 'New Note';
        $data['note_id']    = null;

        return view('note_editor', $data);
    }

    /**
     * Display the editor for an existing note.
     */
    public function edit(int $id)
    {
        helper('cookie');
        if (get_cookie('noteskey') === null) {
            return redirect()->to('/admin/notes/key');
        }

        $data['datatables'] = false;
        $data['js']         = ['vendor/marked.min', 'note-editor', 'markdown-expanders'];
        $data['css']        = ['note-editor'];
        $data['title']      = 'Edit Note';
        $data['note_id']    = $id;

        return view('note_editor', $data);
    }

    /**
     * Return a single decrypted note as JSON.
     */
    public function find(int $id): ResponseInterface
    {
        $notekey = $this->getNotekey();
        if ($notekey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Note key not provided.']);
        }

        $db      = \Config\Database::connect();
        $escaped = $db->escape($notekey);
        $model   = new NoteModel();
        $select  = "id, note_id, AES_DECRYPT(title, {$escaped}) AS title, AES_DECRYPT(body, {$escaped}) AS body, pinned";
        $note    = $model->select($select)->find($id);

        if (! $note) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Note not found.']);
        }

        return $this->response->setJSON($note);
    }

    /**
     * Create a new encrypted note.
     */
    public function create(): ResponseInterface
    {
        $notekey = $this->getNotekey();
        if ($notekey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Note key not provided.']);
        }

        if (! $this->request->is('json')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Expecting JSON data.']);
        }

        $data = $this->request->getJSON(true);
        $data = $this->saveNote($data, 0, $notekey);

        if (isset($data['error'])) {
            return $this->response->setStatusCode(500)->setJSON($data);
        }

        return $this->response->setJSON(['id' => $data['id']]);
    }

    /**
     * Update an existing note, creating a revision if the body changed.
     */
    public function update(int $id): ResponseInterface
    {
        $notekey = $this->getNotekey();
        if ($notekey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Note key not provided.']);
        }

        if (! $this->request->is('json')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Expecting JSON data.']);
        }

        $data = $this->request->getJSON(true);
        $db   = \Config\Database::connect();

        // Pin-only update: update the pinned column directly and return early.
        if (array_key_exists('pinned', $data) && count($data) === 1) {
            $db->table('notes')->where('id', $id)->update(['pinned' => (int) $data['pinned']]);
            return $this->response->setJSON(['id' => $id]);
        }

        // Create a revision when the body has changed (skip for pin-only updates).
        if (! array_key_exists('pinned', $data)) {
            $escaped  = $db->escape($notekey);
            $model    = new NoteModel();
            $select   = "id, note_id, hash, AES_DECRYPT(title, {$escaped}) AS title, AES_DECRYPT(body, {$escaped}) AS body, pinned";
            $existing = $model->select($select)->find($id);

            if ($existing && $existing['body'] !== ($data['body'] ?? '')) {
                $revisionModel = new NoteRevisionModel();
                unset($existing['id'], $existing['created_at'], $existing['updated_at']);
                $existing['title'] = new RawSql("AES_ENCRYPT('" . $db->escapeString($existing['title']) . "', {$escaped})");
                $existing['body']  = new RawSql("AES_ENCRYPT('" . $db->escapeString($existing['body']) . "', {$escaped})");
                $revisionModel->save($existing);
            }
        }

        $data = $this->saveNote($data, $id, $notekey);

        if (isset($data['error'])) {
            return $this->response->setStatusCode(500)->setJSON($data);
        }

        return $this->response->setJSON(['id' => $data['id']]);
    }

    /**
     * List revisions for a note.
     */
    public function listRevisions(int $id): ResponseInterface
    {
        $notekey = $this->getNotekey();
        if ($notekey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Note key not provided.']);
        }

        $model = new NoteModel();
        $note  = $model->select('note_id')->find($id);

        if (! $note) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Note not found.']);
        }

        $db            = \Config\Database::connect();
        $escaped       = $db->escape($notekey);
        $revisionModel = new NoteRevisionModel();
        $revisions     = $revisionModel
            ->select("id, AES_DECRYPT(title, {$escaped}) AS title, created_at")
            ->where('note_id', $note['note_id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON($revisions);
    }

    /**
     * Return a single decrypted revision as JSON.
     */
    public function findRevision(int $id, int $rid): ResponseInterface
    {
        $notekey = $this->getNotekey();
        if ($notekey === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Note key not provided.']);
        }

        $model = new NoteModel();
        $note  = $model->select('note_id')->find($id);

        if (! $note) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Note not found.']);
        }

        $db            = \Config\Database::connect();
        $escaped       = $db->escape($notekey);
        $revisionModel = new NoteRevisionModel();
        $revision      = $revisionModel
            ->select("id, AES_DECRYPT(title, {$escaped}) AS title, AES_DECRYPT(body, {$escaped}) AS body, created_at")
            ->where('note_id', $note['note_id'])
            ->find($rid);

        if (! $revision) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Revision not found.']);
        }

        return $this->response->setJSON($revision);
    }

    /**
     * Delete a note by ID.
     */
    public function delete(int $id): ResponseInterface
    {
        $model = new NoteModel();
        $note  = $model->find($id);

        if (! $note) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Note not found.']);
        }

        $revisionModel = new NoteRevisionModel();
        $revisionModel->where('note_id', $note['note_id'])->delete();

        $model->delete($id);

        return $this->response->setJSON(['status' => 'success', 'deleted' => $id]);
    }

    /**
     * Convert markdown to HTML and return as JSON.
     */
    public function preview(): ResponseInterface
    {
        if (! $this->request->is('json')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Expecting JSON data.']);
        }

        $data     = $this->request->getJSON(true);
        $markdown = $data['markdown'] ?? '';

        if ($markdown === '') {
            return $this->response->setJSON(['html' => '']);
        }

        try {
            $lib = new Markdown();
            $lib->setMarkdown($markdown);
            $result = $lib->convert();
            return $this->response->setJSON(['html' => $result['html'] ?? '']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Preview unavailable.']);
        }
    }

    /**
     * Read the note key from the request header.
     */
    private function getNotekey(): string
    {
        return $this->request->hasHeader('notekey')
            ? $this->request->header('notekey')->getValue()
            : '';
    }

    /**
     * Encrypt and persist a note (insert or update).
     */
    private function saveNote(array $data, int $id, string $notekey): array
    {
        $db      = \Config\Database::connect();
        $escaped = $db->escape($notekey);
        $model   = new NoteModel();

        if (! array_key_exists('pinned', $data)) {
            $body          = $data['body'] ?? '';
            $data['title'] = ($body === '') ? 'Untitled Note' : ltrim(strtok($body, "\n"), '# ');
            $data['hash']  = sha1($notekey);
            $data['title'] = new RawSql("AES_ENCRYPT('" . $db->escapeString($data['title']) . "', {$escaped})");
            $data['body']  = new RawSql("AES_ENCRYPT('" . $db->escapeString($body) . "', {$escaped})");
        }

        try {
            if ($id === 0) {
                $data['note_id'] = Uuid::uuid4()->toString();
                $data['id']      = $model->insert($data);
            } else {
                $model->skipValidation(true)->update($id, $data);
                $data['id'] = $id;
            }
        } catch (\Throwable $e) {
            return ['error' => 'Failed to save note.'];
        }

        return $data;
    }
}
