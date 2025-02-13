<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'file' => 'required|file|max:6000', 
            ]);

            $file = $request->file('file');
            $filePath = save_media($file);
            return get_success_response(['path' => $filePath], "File uploaded successfully");
        } catch (\Throwable $th) {
            // Return error response
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()], 400);
        }

    }

    public function bulkUpload(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|max:50240',
            ]);

            $uploadedFiles = [];

            foreach ($request->file('files') as $index => $file) {
                $uploadedFiles[] = save_media($file);
            }

            // Return success response
            return get_success_response(['files' => (array) $uploadedFiles], "Files uploaded successfully");

        } catch (\Throwable $th) {
            // Return error response
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