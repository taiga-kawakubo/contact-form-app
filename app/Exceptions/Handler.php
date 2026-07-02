<?php

namespace App\Exceptions;

use App\Models\Contact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * APIでお問い合わせが見つからない場合の404レスポンスを登録する。
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            $previous = $e->getPrevious();

            if (
                $request->is('api/v1/contacts/*')
                && $previous instanceof ModelNotFoundException
                && $previous->getModel() === Contact::class
            ) {
                return response()->json([
                    'error' => 'お問い合わせが見つかりませんでした。',
                ], 404);
            }
        });
    }
}
