<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Helpers\ValidationHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Optional;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->only('logout');
    }

    /**
     * 회원가입
     * @param Request $request
     * @return JsonResponse
     */
    public function join(Request $request): JsonResponse
    {
        $validated = $this->validated($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'birthDate' => 'required|date',
            'gender' => [
                'required',
                new Enum(Gender::class)
            ]
        ]);

        $user = User::where('email', $validated['email'])->exists();

        if($user) {
            throw new BadRequestHttpException('이미 존재하는 이메일 입니다.');
        }

        try {
            User::create([
                ...$validated,
                'password' => bcrypt($validated['password'])
            ]);
        } catch (\Exception $exception) {
            throw new HttpException(500, '사용자 생성에 실패했습니다.');
        }

        return $this->sendSuccess();
    }

    /**
     * 로그인
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $this->validated($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $userByEmail = User::where('email', $validated['email'])->first();

        // 입력한 이메일이 없는 경우
        if(!$userByEmail) {
            throw new BadRequestHttpException('존재하지 않는 이메일 입니다.');
        }

        // 로그인 시도하여 실패한 경우

        if(!Auth::attempt($validated)) {
            throw new BadRequestHttpException('이메일 또는 비밀번호를 확인하세요.');
        }

        $access = Auth::user()->createToken('accessToken');
        $refresh = Auth::user()->createToken('refreshToken');

        $user = Auth::user();

        return response()->json([
            'accessToken' => $access->plainTextToken,
            'refreshToken' => $refresh->plainTextToken,
            'user' => $user->only(['id', 'name', 'isAdmin'])
        ]);
    }

    /**
     * 로그아웃
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return $this->sendSuccess();
    }

    /**
     * 토큰 생신
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();

        if(!$user) {
            throw new HttpException(401, '로그인된 사용자가 아닙니다.');
        }

        $access = Auth::user()->createToken('accessToken');
        $refresh = Auth::user()->createToken('refreshToken');

        return response()->json([
            'accessToken' => $access->plainTextToken,
            'refreshToken' => $refresh->plainTextToken,
        ]);
    }




    // private
    private function validated(array $data, array $rules)
    {
        $validator = Validator::make($data, $rules);

        return ValidationHelper::checkValidator($validator);
    }

    private function sendSuccess(): JsonResponse
    {
        return response()->json(['result' => 'success']);
    }
}
