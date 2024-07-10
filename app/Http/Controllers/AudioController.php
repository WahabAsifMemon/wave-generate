<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'audio' => 'required|mimes:mp3,wav',
        ]);

        $path = $request->file('audio')->store('public/audio');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
