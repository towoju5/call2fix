<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max size
                'type' => 'required|in:image,video,document',
            ]);

            $file = $request->file('file');
            $type = $request->input('type');

            // Additional validations based on file type
            switch ($type) {
                case 'image':
                    $request->validate(['file' => 'image|mimes:jpeg,png,jpg,gif|max:2048']); // 2MB max for images
                    break;
                case 'video':
                    $request->validate(['file' => 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:10240']); // 60MB max for videos
                    break;
                case 'document':
                    $request->validate(['file' => 'mimes:pdf,doc,docx,txt|max:5120']); // 5MB max for documents
                    break;
            }

            $path = save_media($file);

            return get_success_response(['path' => $path], "File upploaded successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()], 400);
        }
    }

    public function fetch($path)
    {
        try {
            if (str_contains($path, 'https://alphamead.lon1.digitaloceanspaces.com/')) {
                $path = str_replace("https://alphamead.lon1.digitaloceanspaces.com/", "", $path);
            }
            // Check if the file exists in DigitalOcean Spaces
            if (!Storage::disk('spaces')->exists($path)) {
                throw new \Exception('File not found');
            }

            // Get the file contents
            $file = Storage::disk('spaces')->get($path);

            // Get the mime type
            $mime = Storage::disk('spaces')->mimeType($path);

            return response($file, 200)->header('Content-Type', $mime);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()], 400);
        }
    }

    public function destroy($path)
    {
        try {
            if (str_contains($path, 'https://alphamead.lon1.digitaloceanspaces.com/')) {
                $path = str_replace("https://alphamead.lon1.digitaloceanspaces.com/", "", $path);
            }
            // Check if the file exists in DigitalOcean Spaces
            if (!Storage::disk('spaces')->exists($path)) {
                throw new \Exception('File not found');
            }

            // Delete the file
            Storage::disk('spaces')->delete($path);

            return response()->json(['success' => true, 'message' => 'File deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'error' => $th->getMessage()], 400);
        }
    }
}
