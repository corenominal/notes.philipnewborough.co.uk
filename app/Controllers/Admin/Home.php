<?php

namespace App\Controllers\Admin;

use App\Models\NoteModel;
use App\Models\NoteRevisionModel;

class Home extends BaseController
{
    /**
     * Display the Admin Dashboard page.
     *
     * @return string Rendered admin dashboard view output.
     */
    public function index()
    {
        $noteModel     = new NoteModel();
        $revisionModel = new NoteRevisionModel();

        $data['stats'] = [
            'total_notes'     => $noteModel->countAll(),
            'pinned_notes'    => $noteModel->where('pinned', 1)->countAllResults(),
            'total_revisions' => $revisionModel->countAll(),
        ];

        $data['recent_notes'] = $noteModel
            ->select('id, note_id, hash, pinned, created_at, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->limit(10)
            ->findAll();

        $data['js']    = ['admin/home'];
        $data['css']   = ['admin/home'];
        $data['title'] = 'Admin Dashboard';

        return view('admin/home', $data);
    }
}
