<?php

namespace App\Controllers\Admin;

use App\Models\NoteModel;

class Export extends BaseController
{
    public function index()
    {
        helper('cookie');
        if (get_cookie('noteskey') === null) {
            return redirect()->to('/admin/notes/key');
        }

        $noteModel = new NoteModel();

        $data['title']      = 'Export Notes';
        $data['js']         = ['admin/export'];
        $data['note_count'] = $noteModel->countAll();

        return view('admin/export', $data);
    }

    public function process()
    {
        $notekey = trim($this->request->getPost('notekey') ?? '');

        if ($notekey === '') {
            return redirect()->to('/admin/export')->with('error', 'A notes key is required to decrypt and export notes.');
        }

        $db      = \Config\Database::connect();
        $escaped = $db->escape($notekey);

        $rows = $db->table('notes')
            ->select("note_id, hash, AES_DECRYPT(title, {$escaped}) AS title, AES_DECRYPT(body, {$escaped}) AS body, pinned, created_at, updated_at")
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $notes = [];
        foreach ($rows as $row) {
            $notes[] = [
                'note_id'    => $row['note_id'],
                'hash'       => $row['hash'],
                'title'      => $row['title'] !== null ? $row['title'] : '',
                'body'       => $row['body'] !== null ? $row['body'] : '',
                'pinned'     => (int) $row['pinned'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        $json     = json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'notes-export-' . date('Y-m-d') . '.json';

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($json))
            ->setBody($json);
    }
}
