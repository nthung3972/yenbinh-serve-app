<?php
namespace App\Services\ApiAdmin;

// use App\Models\Blog;
use App\Repositories\BuildingRepository;
// use App\Repositories\CategoryRepository;
// use App\Repositories\CommentRepository;
// use Exception;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    // public function __construct(
    //     public BuildingRepository $buildingRepository,
    // ) {
    // }

    public function loginWeb($userInfo)
    {
        $token = auth('api')->attempt($userInfo);
        if (!$token) {
            return ['error' => 1, 'message' => __('M41')];
        } else {
            $user = auth('api')->user();
            return [
                'token' => $token,
                'user' => $user
            ];
        }
    }

}