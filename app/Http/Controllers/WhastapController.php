<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class WhastapController extends Controller
{
    
    public function UploadPdfCloud(Request $r)
    {
        try {
            $validator = Validator::make($r->all(), [
                'file'      => 'required|file|mimes:pdf|max:5120',
                'nama_file' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $file      = $r->file('file');
            $namaFile  = $r->input('nama_file');
            $extension = $file->getClientOriginalExtension();

            $namaFile = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaFile);
            $fileName = $namaFile . '_' . time() . '.' . $extension;


            $filePath = $fileName;

            $uploaded = Storage::disk('r2')->put(
                $filePath,
                file_get_contents($file->getRealPath())
            );

            if (!$uploaded) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal mengupload file ke R2',
                ], 500);
            }

            $publicBase = rtrim(env('CLOUDFLARE_R2_PUBLIC_URL'), '/');
            $url        = $publicBase . '/' . $fileName;

            return response()->json([
                'status'  => 'success',
                'message' => 'File berhasil diupload ke Cloudflare R2',
                'path'    => $filePath,
                'url'     => $url,
                'name'    => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
