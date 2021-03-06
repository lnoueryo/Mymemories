<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Like;
use App\Comment;
use App\Post;
use App\Follower;
use App\Http\Resources\UserCollection;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request) {
        $request_id = $request->id;
        $main_user = User::with(['likes', 'comments','followers'])->find(Auth::id());
        return view('profile', compact('main_user', 'request_id'));
    }

    public function logout(){
        return Auth::logout();
    }

    public function create(Request $request) {
        if($request->followed == 0) {
            $follower = new Follower;
            $follower->following_id = $request->myId;
            $follower->followed_id = $request->id;
            $follower->save();
            return  1;
        } else {
            $follower = Follower::where('following_id', $request->myId)->where('followed_id', $request->id);
            $follower->delete();
            return  0;
        }
    }
    public function deletePost(Request $request) {
        $decoded_post = json_decode($request->post);
        $post = Post::find($decoded_post->id);
        $image = $decoded_post->image;
        for($i=0; $i<count($image); $i++){
            Storage::delete($image[$i]);
        };
        $post->delete();
        $posts = Post::with(['likes' => function($like_query){
            $like_query->with(['user'])->orderBy('updated_at', 'desc');
        }, 'comments' => function($comment_query){
            $comment_query->with(['user'])->orderBy('updated_at', 'desc');
        }])->where('user_id', $post->user_id)->orderBy('updated_at', 'desc')->get();
        return $posts;
    }

    public function delete(Request $request) {
        $follower = Follower::where('following_id', Auth::id())->where('followed_id', $request->id);
        $follower->delete();
        return  redirect()->route('profile', ['id' => $request->id]);
    }

    public function uploadBg(Request $request) {
        $user = User::find($request->userId);
        Storage::disk('background')->delete($user->bg_image);
        $image = $request->bgData;
        if(strpos($image,'data:image/png;') !== false){
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).'.'.'png';
        } else {
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).'.'.'jpg';
        }
        Storage::disk('background')->put($imageName, base64_decode($image));
        $user->bg_image = $imageName;
        $user->save();
        return $user->bg_image;
    }
    public function uploadAvatar(Request $request) {
        $user = User::find($request->userId);
        Storage::disk('avatar')->delete($user->profile_image);
        $image = $request->avatarData;
        if(strpos($image,'data:image/png;') !== false){
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).'.'.'png';
        } else {
            $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).'.'.'jpg';
        }
        Storage::disk('avatar')->put($imageName, base64_decode($image));
        $user->profile_image = $imageName;
        $user->save();
        return $user->profile_image;
    }
}
