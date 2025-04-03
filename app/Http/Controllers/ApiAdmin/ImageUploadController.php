<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Response;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validate file
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $extension = $file->getClientOriginalExtension();

            $sanitizedName = $this->sanitizeFilename($originalName);

            $fileName = time() . '_' . $sanitizedName . '.' . $extension;

            $fileContent = file_get_contents($file->getRealPath());

            $supabase = app('supabase');

            try {
                $result = $supabase->__getStorage()->from('images')->upload(
                    $fileName,
                    $fileContent,
                    ['contentType' => $file->getMimeType()]
                );

                $fileUrl = $supabase->__getStorage()->from('images')->getPublicUrl($fileName);
                // return response()->json([
                //     'success' => true,
                //     'message' => 'Image uploaded successfully',
                //     'path' => $fileUrl,
                //     'filename' => $fileName // Trả về tên file đã upload
                // ]);
                return Response::data(['path' => $fileUrl]);
            } catch (\Throwable $th) {
                // return response()->json([
                //     'success' => false,
                //     'message' => 'Upload failed: ' . $e->getMessage(),
                // ], 500);
                return Response::dataError($th->getCode(), ['error' => [$th->getMessage()]], $th->getMessage());
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image provided',
        ], 400);
    }

    // Hàm hỗ trợ xử lý tên file
    private function sanitizeFilename($filename)
    {
        // Chuyển đổi ký tự có dấu thành không dấu
        $filename = preg_replace('/[áàảãạâấầẩẫậăắằẳẵặ]/u', 'a', $filename);
        $filename = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $filename);
        $filename = preg_replace('/[íìỉĩị]/u', 'i', $filename);
        $filename = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $filename);
        $filename = preg_replace('/[úùủũụưứừửữự]/u', 'u', $filename);
        $filename = preg_replace('/[ýỳỷỹỵ]/u', 'y', $filename);
        $filename = preg_replace('/[đ]/u', 'd', $filename);
        $filename = preg_replace('/[ÁÀẢÃẠÂẤẦẨẪẬĂẮẰẲẴẶ]/u', 'A', $filename);
        $filename = preg_replace('/[ÉÈẺẼẸÊẾỀỂỄỆ]/u', 'E', $filename);
        $filename = preg_replace('/[ÍÌỈĨỊ]/u', 'I', $filename);
        $filename = preg_replace('/[ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢ]/u', 'O', $filename);
        $filename = preg_replace('/[ÚÙỦŨỤƯỨỪỬỮỰ]/u', 'U', $filename);
        $filename = preg_replace('/[ÝỲỶỸỴ]/u', 'Y', $filename);
        $filename = preg_replace('/[Đ]/u', 'D', $filename);

        // Thay thế các ký tự không phải chữ cái, số bằng dấu gạch dưới
        $filename = preg_replace('/[^a-zA-Z0-9-]/', '_', $filename);

        // Loại bỏ nhiều dấu gạch dưới liên tiếp
        $filename = preg_replace('/_+/', '_', $filename);

        // Loại bỏ dấu gạch dưới ở đầu và cuối
        $filename = trim($filename, '_');

        return $filename;
    }
}
