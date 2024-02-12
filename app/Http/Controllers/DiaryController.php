<?php

namespace App\Http\Controllers;

use App\Models\Diary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DiaryController extends Controller
{
    public function diaryList(Request $request): JsonResponse
    {
        $diaries = Diary::with(['author' => fn ($q) => $q->select(['id', 'name'])])
            ->get();

        foreach ($diaries as $diary) {
            if($diary->image) {
                $diary->image = config('app.url') . '/image/diaries/' . $diary->image;
            }
        }

        return response()->json($diaries);
    }

    public function diaryDetail(Request $request, int $diaryId): JsonResponse
    {
        $diary = Diary::where('id', $diaryId)
            ->with('author')
            ->first();

        if($diary->image) {
            $diary->image = config('app.url') . '/image/diaries/' . $diary->image;
        }

        return response()->json($diary);
    }

    public function createDiary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'weight' => 'numeric',
            'height' => 'numeric',
            'file' => 'file'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $fileName = null;

        if(isset($validated['file'])) {
            $fileName = Uuid::uuid4() . '.' . $validated['file']->extension();
            Storage::disk('local')->putFileAs('diaries', $validated['file'], $fileName);
        }

        $user = $request->user();

        Diary::create([
            ...$validated,
            'userId' => $user->id,
            'file' => $fileName
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function editDiary(Request $request, int $diaryId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'content' => 'string',
            'weight' => 'numeric',
            'height' => 'numeric'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $diary = Diary::where('id', $diaryId)->first();

        if(!$diary) {
            throw new NotFoundHttpException('존재하지 않는 일기 입니다.');
        }

        $diary->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'weight' => $validated['weight'],
            'height' => $validated['height']
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function deleteDiary(Request $request, int $diaryId): JsonResponse
    {
        $userId = $request->user()->id;

        $diary = Diary::where('id', $diaryId)->where('userId', $userId)->first();

        if(!$diary) {
            throw new NotFoundHttpException('존재하지 않는 일기 입니다.');
        }

        $diary->delete();

        return response()->json(['result' => 'OK']);
    }
}
