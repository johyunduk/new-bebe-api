<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\MallItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MallController extends Controller
{
    public function sizeList(Request $request): JsonResponse
    {
        return response()->json([
            ['id' => 1, 'name' => 'S'],
            ['id' => 2, 'name' => 'M'],
            ['id' => 3, 'name' => 'L'],
            ['id' => 4, 'name' => 'XL'],
            ['id' => 5, 'name' => 'XXL'],
        ]);
    }

    public function categoryList(Request $request): JsonResponse
    {
        $categories = Category::select(['id', 'name'])->get();

        return response()->json($categories);
    }

    public function createCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),['name' => 'required|string']);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        Category::create([
            'name' => $validated['name']
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function createItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'required|int',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'file' => 'required|file'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $fileName = Uuid::uuid4() . '.' . $validated['file']->extension();

        Storage::disk('local')->putFileAs('items', $validated['file'], $fileName);

        $newItem = MallItem::create([
            ...$validated,
            'image' => $fileName
        ]);

        ItemCategory::create([
            'mallItemId' => $newItem->id,
            'categoryId' => $validated['categoryId']
        ]);

        return response()->json(['result' => 'OK']);
    }

    public function itemList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'int',
            'page' => 'int'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = optional($validator->getData());

        $items = MallItem::when($validated['categoryId'], fn ($q) => $q->where('categoryId', $validated['categoryId']))
            ->with(['itemCategories' => fn ($q) => $q->with('category')])
            ->orderBy('id', 'desc')
            ->offset(($validated['page'] - 1) * 10)
            ->limit(10)
            ->get();

        foreach ($items as $item) {
            if($item->image) {
                $item->image = config('app.url') . '/image/items/' . $item->image;
            }
        }

        return response()->json([
            'count' => MallItem::count(),
            'items' => $items,
        ]);
    }

    public function itemDetail(Request $request, int $itemId): JsonResponse
    {
        $item = MallItem::where('id', $itemId)
            ->with([
                'itemCategories' => fn ($q) => $q->select(['id', 'categoryId', 'mallItemId'])
                    ->with(['category' => fn ($q) => $q->select(['id', 'name'])])
            ])
            ->first();

        if(!$item) {
            throw new NotFoundHttpException('존재하지 않는 상품 입니다.');
        }

        if($item->image) {
            $item->image = config('app.url') . '/image/items/' . $item->image;
        }

        return response()->json($item);
    }

    public function editItem(Request $request, int $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categoryId' => 'int',
            'name' => 'string',
            'price' => 'numeric',
            'description' => 'string',
            'file' => 'file'
        ]);

        if($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->messages());
        }

        $validated = $validator->getData();

        $item = MallItem::where('id', $itemId)->first();

        if(!$item) {
            throw new NotFoundHttpException('존재하지 않는 상품 입니다.');
        }

        $fileName = $item->image;
        if(isset($validated['file'])) {
            Storage::disk('local')->exists("items/{$item->image}");
            Storage::disk('local')->delete("items/{$item->image}");

            $fileName = Uuid::uuid4() . '.' . $validated['file']->extension();

            Storage::disk('local')->putFileAs('items', $validated['file'], $fileName);
        }

        $item->update([
            ...$validated,
            'image' => $fileName
        ]);

        $itemCategory = ItemCategory::where('mallItemId', $itemId)->first();

        $itemCategory->update([
            'categoryId' => $validated['categoryId']
        ]);

        return response()->json(['result' => 'OK']);
    }
}
