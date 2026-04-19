<?php

namespace App\Controllers\Admin;

use CodeIgniter\Database\RawSql;

class Import extends BaseController
{
    public function index()
    {
        helper('cookie');
        if (get_cookie('noteskey') === null) {
            return redirect()->to('/admin/notes/key');
        }

        $data['title'] = 'Import Notes';
        $data['js']    = ['admin/import'];

        return view('admin/import', $data);
    }

    public function process()
    {
        $notekey = trim($this->request->getPost('notekey') ?? '');

        if ($notekey === '') {
            return redirect()->to('/admin/import')->with('error', 'A notes key is required to encrypt imported notes.');
        }

        $file = $this->request->getFile('import_file');

        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/import')->with('error', 'Please upload a valid JSON file.');
        }

        if (strtolower($file->getClientExtension()) !== 'json') {
            return redirect()->to('/admin/import')->with('error', 'Only JSON files are accepted.');
        }

        $content = file_get_contents($file->getTempName());
        $notes   = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($notes)) {
            return redirect()->to('/admin/import')->with('error', 'Invalid JSON: ' . json_last_error_msg());
        }

        $db      = \Config\Database::connect();
        $escaped = $db->escape($notekey);
        $hash    = sha1($notekey);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($notes as $index => $note) {
            $rowLabel = 'Row ' . ($index + 1);

            if (empty($note['note_id']) || empty($note['title']) || empty($note['body'])) {
                $errors[] = "{$rowLabel}: missing required fields (note_id, title, body).";
                $skipped++;
                continue;
            }

            $exists = $db->table('notes')
                ->where('note_id', $note['note_id'])
                ->countAllResults();

            if ($exists > 0) {
                $skipped++;
                continue;
            }

            $record = [
                'note_id'    => $note['note_id'],
                'hash'       => $hash,
                'title'      => new RawSql("AES_ENCRYPT('" . $db->escapeString($note['title']) . "', {$escaped})"),
                'body'       => new RawSql("AES_ENCRYPT('" . $db->escapeString($note['body']) . "', {$escaped})"),
                'pinned'     => isset($note['pinned']) ? (int) $note['pinned'] : 0,
                'created_at' => $note['created_at'] ?? date('Y-m-d H:i:s'),
                'updated_at' => $note['updated_at'] ?? date('Y-m-d H:i:s'),
            ];

            if ($db->table('notes')->insert($record)) {
                $imported++;
            } else {
                $errors[] = "{$rowLabel}: database insert failed.";
                $skipped++;
            }
        }

        $data['title']    = 'Import Notes';
        $data['imported'] = $imported;
        $data['skipped']  = $skipped;
        $data['errors']   = $errors;
        $data['total']    = count($notes);

        return view('admin/import', $data);
    }
}
