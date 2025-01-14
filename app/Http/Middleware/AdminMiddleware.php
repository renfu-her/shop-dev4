<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;


class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 檢查用戶是否已登入且是管理員
        if (Auth::check() && !Auth::user()->is_admin) {
            // 登出當前用戶
            Auth::logout();

            // 使用 Filament 的通知系統顯示錯誤訊息
            Notification::make()
                ->title('權限不足')
                ->body('只有管理員才能訪問後台系統')
                ->danger()
                ->send();

            // 重定向到登入頁面
            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}
