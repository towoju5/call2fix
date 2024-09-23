<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use Validator;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'max:10240', // 10MB max size
                'type' => 'required|in:image,video,document',
            ]);

            $type = $request->input('type');
            $uploadedFiles = [];

            foreach ($request->file('files') as $file) {
                // Additional validations based on file type
                $validator = Validator::make(['file' => $file], [
                    'file' => [
                        'required',
                        function ($attribute, $value, $fail) use ($type) {
                            switch ($type) {
                                case 'image':
                                    if (!in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']) || $value->getSize() > 2048000) {
                                        $fail('The file must be an image (jpeg, png, jpg, gif) and not exceed 2MB.');
                                    }
                                    break;
                                case 'video':
                                    if (!in_array($value->getMimeType(), ['video/avi', 'video/mpeg', 'video/quicktime', 'video/mp4']) || $value->getSize() > 10240000) {
                                        $fail('The file must be a video (avi, mpeg, quicktime, mp4) and not exceed 10MB.');
                                    }
                                    break;
                                case 'document':
                                    if (!in_array($value->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'txt']) || $value->getSize() > 5120000) {
                                        $fail('The file must be a document (pdf, doc, docx, txt) and not exceed 5MB.');
                                    }
                                    break;
                            }
                        },
                    ],
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->first()], 422);
                }

                $path = save_media($file);
                if (empty($path)) {
                    return response()->json(['error' => 'Failed to save file. Path cannot be empty.'], 500);
                }
                $uploadedFiles[] = $path;
            }
            return get_success_response(['paths' => $uploadedFiles], "Files uploaded successfully");
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
