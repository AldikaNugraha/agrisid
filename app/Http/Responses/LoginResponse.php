<?php
namespace App\Http\Responses;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): RedirectResponse|Redirector
    {
        // return whatever you want as url
        $url = 'http://localhost:8000/admin/villages';
        return redirect()->intended($url);
    }
}
