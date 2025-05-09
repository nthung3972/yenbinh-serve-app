<?php

namespace App\Http\Controllers\ApiAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\Response;
use App\Http\Requests\UploadFileRequest\UploadImageRequest;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(UploadImageRequest $request)
    {
        if ($request->hasFile('image')) {
            $folder = $request->input('folder', 'uploads');

            $file = $request->file('image');

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $sanitizedName = $this->sanitizeFilename($originalName);

            $extension = $file->getClientOriginalExtension();

            $fileName = $folder . '_' . time() . '_' . $sanitizedName . '.' . $extension;

            // dd([
            //     'APP_URL' => config('app.url'),
            //     'Storage URL' => Storage::url("{$folder}/{$fileName}"),
            //     'Full URL' => asset(Storage::url("{$folder}/{$fileName}")),
            // ]);

            try {
                // Xóa ảnh cũ nếu được truyền vào
                if ($request->filled('old_path')) {
                    $oldUrl = $request->input('old_path');
                    $baseUrl = rtrim(config('app.url'), '/');
                
                    // Loại bỏ phần domain để còn lại path: storage/building/xxx.png
                    $relativePath = str_replace("{$baseUrl}/storage/", '', $oldUrl);
                
                    // Xóa file trong storage/app/public
                    Storage::disk('public')->delete($relativePath);
                }

                // Lưu ảnh mới
                $path = $file->storeAs("public/{$folder}", $fileName);

                // Trả về URL đầy đủ
                $relativePath = Storage::url("{$folder}/{$fileName}");
                $baseUrl = config('app.url');
                $url = rtrim($baseUrl, '/') . $relativePath;

                return Response::data(['path' => $url]);
            } catch (\Throwable $th) {
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
