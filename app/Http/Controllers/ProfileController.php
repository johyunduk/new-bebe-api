<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ProfileController extends Controller
{
    public function getProfile(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $user = User::where('id', $userId)->first();

        if(!$user) {
            throw new NotFoundHttpException('존재하지 않는 사용자 입니다.');
        }

        $user->avatar = $user->avatar ? config('app.url') . '/image/avatar/' . $user->avatar : null;

        return response()->json($user);
    }

    public function editProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'gender' => new Enum(Gender::class),
            'birthDate' => 'date'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $userId = $request->user()->id;

        $user = User::where('id', $userId)->first();

        if(!$user) {
            throw new NotFoundHttpException('존재하지 않는 사용자 입니다.');
        }

        $user->update($validated);

        return response()->json(['result' => 'OK']);
    }

    public function editAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $userId = $request->user()->id;

        $user = User::where('id', $userId)->first();

        if(!$user) {
            throw new NotFoundHttpException('존재하지 않는 사용자 입니다.');
        }

        $fileName = Uuid::uuid4() . '.' . $validated['file']->extension();

        Storage::disk('local')->putFileAs('avatars', $validated['file'], $fileName);

        $user->update([
            'avatar' => $fileName
        ]);

        return response()->json(['result' => 'OK']);
    }
}
