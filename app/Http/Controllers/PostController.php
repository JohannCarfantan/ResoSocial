<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Like;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['type' => 'required', 'message' => 'required_if:postImage,==,NULL|required_if:postVideo,==,NULL', 'postImage' => 'required_if:message,==,NULL|required_if:postVideo,==,NULL', 'postVideo' => 'required_if:message,==,NULL|required_if:postImage,==,NULL']);

        if ($validator->fails()) {
            $notification = array(
                'message' => 'Veuillez uploader un fichier.',
                'alert-type' => 'error'
            );

            return back()->with($notification);
        } else {

            $user = User::find(Auth::user()->id);

            if ($request->hasFile('postImage')) {
                if ($request->file('postImage')->isValid()) {
                    $path = $request->postImage->store('public');
                    $goodPath = str_replace('public/', '', $path);
                    $post = Post::preparePost($request->type, $goodPath);
                    $user->posts()->save($post);
                }
            } elseif ($request->hasFile('postVideo')) {
                if ($request->file('postVideo')->isValid()) {
                    $path = $request->postVideo->store('public');
                    $goodPath = str_replace('public/', '', $path);
                    $post = Post::preparePost($request->type, $goodPath);
                    $user->posts()->save($post);
                }
            } else {
                $post = Post::preparePost($request->type, null, $request->message);
                $user->posts()->save($post);
            }

            $notification = array(
                'message' => 'Post créé',
                'alert-type' => 'success'
            );

            return back()->with($notification);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);

        if ($validator->fails()) {
            $notification = array(
                'message' => 'Oups, quelque chose s\'est mal passé, veuillez réessayer.',
                'alert-type' => 'error'
            );

            return back()->with($notification);
        } else {
            Post::find($request->id)->delete();

            $notification = array(
                'message' => 'Ce post a bien été supprimé',
                'alert-type' => 'success'
            );

            return back()->with($notification);
        }
    }

    public function like(Request $request)
    {
        $validator = Validator::make($request->all(), ['postId' => 'required']);

        if ($validator->fails()) {
            $notification = array(
                'message' => 'Oups, quelque chose s\'est mal passé, veuillez réessayer.',
                'alert-type' => 'error'
            );

            return back()->with($notification);
        } else {
            $existing_like = Like::withTrashed()->wherePostId($request->postId)->whereUserId(Auth::id())->first();

            if (is_null($existing_like)) {
                Like::create([
                    'post_id' => $request->postId,
                    'user_id' => Auth::id()
                ]);
            } else {
                if (is_null($existing_like->deleted_at)) {
                    $existing_like->delete();
                } else {
                    $existing_like->restore();
                }
            }

            $notification = array(
                'message' => 'Liked',
                'alert-type' => 'success'
            );

            return back()->with($notification);
        }
    }
}
