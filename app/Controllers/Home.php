<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\NoteModel;

class Home extends BaseController
{
    /**
     * Display the home page
     */
    public function index()
    {
        helper('cookie');
        if (get_cookie('noteskey') === null) {
            return redirect()->to('/admin/notes/key');
        }

        $data['datatables'] = false;
        $data['js']         = ['home'];
        $data['css']        = ['home'];
        $data['title']      = 'Notes';
        return view('home', $data);
    }

    /**
     * JSON endpoint for notes list with optional search.
     *
     * Accepts an optional `q` GET parameter to filter by title.
     *
     * @return ResponseInterface
     */
    public function list(): ResponseInterface
    {
        helper('cookie');
        $notekey = get_cookie('noteskey');
        if ($notekey === null) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Not authorised.']);
        }

        $db      = \Config\Database::connect();
        $escaped = $db->escape($notekey);
        $q       = trim($this->request->getGet('q') ?? '');
        $perPage = 20;
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $model = new NoteModel();
        $notes = $model
            ->select("id, AES_DECRYPT(title, {$escaped}) AS title, AES_DECRYPT(body, {$escaped}) AS body, pinned, updated_at")
            ->orderBy('pinned', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        if ($q !== '') {
            $notes = array_values(
                array_filter($notes, fn($n) => stripos($n['title'] ?? '', $q) !== false || stripos($n['body'] ?? '', $q) !== false)
            );
        }

        $total      = count($notes);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min($page, $totalPages);
        $notes      = array_slice($notes, ($page - 1) * $perPage, $perPage);
        $notes      = array_map(fn($n) => array_diff_key($n, ['body' => '']), $notes);

        return $this->response->setJSON([
            'notes'       => $notes,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages,
        ]);
    }
}

