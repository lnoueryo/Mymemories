@extends('layouts.app')

@section('content')
<profile :my-info='@json(Auth::user())' :this-user-posts='@json($this_user_posts)' :this-user='@json($this_user)' :my-likes='@json($my_likes)' :this-user-likes='@json($this_user_likes)' :followed='@json($followed)'></profile>
@endsection
