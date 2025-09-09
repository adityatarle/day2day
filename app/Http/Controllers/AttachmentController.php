<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AttachmentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attachable_type' => 'required|string|in:shipment,receipt,discrepancy',
            'attachable_id' => 'required|integer',
            'file' => 'required|file|max:10240',
            'category' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $map = [
            'shipment' => \App\Models\Shipment::class,
            'receipt' => \App\Models\Receipt::class,
            'discrepancy' => \App\Models\Discrepancy::class,
        ];

        $modelClass = $map[$request->attachable_type];
        $attachable = $modelClass::findOrFail($request->attachable_id);

        $attachment = $attachable->attachments()->create([
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'category' => $request->category,
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json(['status' => 'success', 'data' => $attachment], 201);
    }
}

