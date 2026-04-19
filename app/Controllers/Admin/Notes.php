<?php

namespace App\Controllers\Admin;

class Notes extends BaseController
{
    public function key()
    {
        $data['title'] = 'Notes - Set Key';
        $data['js']    = ['admin/notes-key'];

        return view('admin/notes_key', $data);
    }
}
