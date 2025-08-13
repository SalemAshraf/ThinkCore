public function boot()
{
    $this->routes(function () {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        // You might need something like this for your admin-api.php:
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api/admin-api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    });
}
