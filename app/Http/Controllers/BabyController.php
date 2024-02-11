<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Models\UserBaby;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BabyController extends Controller
{
    public function babyList(Request $request): JsonResponse
    {
        $user = $request->user();

        $babies = UserBaby::where('userId', $user->id)->orderBy('id', 'desc')->get();

        foreach ($babies as $baby) {
            $baby->face = $baby->face ? config('app.url') . '/image/faces/' . $baby->face : null;
        }

        return response()->json($babies);
    }

    public function createBaby(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'birthDate' => 'string',
            'gender' => new Enum(Gender::class),
            'expectDate' => 'date',
            'pregnantDate' => 'date'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $userId = $request->user()->id;

        $babies = UserBaby::where('userId', $userId)->get();

        if(count($babies) > 0) {
            throw new BadRequestHttpException('이미 등록된 아이가 있습니다.');
        }

        UserBaby::create([
            ...$validated,
            'userId' => $userId
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function editBaby(Request $request, int $babyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'birthDate' => 'string',
            'gender' => new Enum(Gender::class),
            'expectDate' => 'date',
            'pregnantDate' => 'date'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $baby = UserBaby::where('id', $babyId)->first();

        if(!$baby) {
            throw new NotFoundHttpException('존재하지 않는 아이 입니다.');
        }

        $baby->update([$validated]);

        return response()->json(['result' => 'OK']);
    }

    public function deleteBaby(Request $request, int $babyId): JsonResponse
    {
        $baby = UserBaby::where('id', $babyId)->first();

        if(!$baby) {
            throw new NotFoundHttpException('존재하지 않는 아이 입니다.');
        }

        $baby->delete();

        return response()->json(['result' => 'OK']);
    }

    public function editBabyFace(Request $request, int $babyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $baby = UserBaby::where('id', $babyId)->first();

        if(!$baby) {
            throw new NotFoundHttpException('존재하지 않는 아이 입니다.');
        }

        $fileName = Uuid::uuid4() . '.' . $validated['file']->extension();

        Storage::disk('local')->putFileAs('faces', $validated['file'], $fileName);

        $baby->update([
            'face' => $fileName
        ]);

        return response()->json(['result' => 'OK']);
    }
}
