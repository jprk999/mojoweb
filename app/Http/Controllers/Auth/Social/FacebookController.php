<?php

namespace App\Http\Controllers\Auth\social;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function facebookRedirect()
    {
        $redirectUrl = Socialite::driver('facebook')->stateless()->usingGraphVersion('v20.0')->redirect()->getTargetUrl();
        \Log::info('Facebook Redirect URL: ' . $redirectUrl);
        return Socialite::driver('facebook')->with(['redirect_uri' => "https://localhost/auth/facebook/callback/", 'config_id' => '1063495474617185'])->usingGraphVersion('v20.0')->stateless()->scopes(['email', 'pages_show_list', 'pages_read_engagement'])->redirect();
    }

    public function facebookCallback()
    {
        \Log::info('Handling Facebook Callback');
        try {
            $user = Socialite::driver('facebook')->with(['redirect_uri' => "https://localhost/auth/facebook/callback/", 'config_id' => '1063495474617185'])->usingGraphVersion('v20.0')->stateless()->user();
            // Fetch Pages using the Facebook Graph API
            $response = Http::withToken($user->token)
            ->get('https://graph.facebook.com/v20.0/me/accounts');

            if ($response->successful()) {
                $pages = $response->json()['data'];
                return Inertia::render('List', ['pages' => $pages, 'token' => $user->token, 'userPicture' => $user->avatar_original . "&access_token={$user->token}",'userName'=>$user->name, 'status' => session('status')]);
            } else {
                return redirect()->route('login')->with('error', 'Failed to retrieve Facebook pages.');
            }

            
            // \Log::info('User Information: '.$user);
            // Redirect to desired route'
            print_r($user);exit;
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            \Log::error('Facebook Login Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('login')->withErrors('Failed to authenticate with Facebook.');
        }
    }

    public function fetchPageInsights(Request $request)
    {
        $pageId = $request->input('page_id');
        $token = $request->input('page_token'); 

        $response = Http::withToken($token)->get("https://graph.facebook.com/v20.0/{$pageId}/insights", [
            'metric' => 'page_fans,page_post_engagements,page_impressions,page_actions_post_reactions_total'
        ]);
        // print_r($response);exit;
        return response()->json($response->json());
    }



}
