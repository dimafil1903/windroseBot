@extends('voyager::master')
@section('content')
    <div class="page-content container-fluid">
        <form class="form-edit-add" role="form"  method="POST" action="{{url('admin/telegram/send')}}" enctype="multipart/form-data">
            @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
            @endif
            {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <i class="voyager-character"></i> {{ __('voyager::post.title') }}
                                    <span class="panel-desc"> {{ __('voyager::post.title_sub') }}</span>
                                </h3>
                                <div class="panel-actions">
                                    <a class="panel-action voyager-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <input type="text" required class="form-control" maxlength="60" id="title" name="title" placeholder="{{ __('voyager::generic.title') }}" value="{{ $dataTypeContent->title ?? '' }}">
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">{{ __('voyager::post.content') }}</h3>
                                <div class="panel-actions">
                                    <a class="panel-action voyager-resize-full" data-toggle="panel-fullscreen" aria-hidden="true"></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">
                                    <textarea required name="body" maxlength="950 " id="body" class="form-control"></textarea>
                                </div>

                            </div>
                            <div class="form-group container">
                                <label for="exampleFormControlFile1">Example file input</label>
                                <input type="file" name="photo" class="form-control-file" id="exampleFormControlFile1">
                            </div>
                            <div class="form-group container">
                                <label for="exampleFormControlSelect1">Example select</label>
                                <select class="form-control" name="chat_id" required id="exampleFormControlSelect1">
                                    <option  value="all">
                                        Send to all users
                                    </option>
                                    @foreach($chats as $chat)

                                        <option value="{{$chat->id}}">
                                            @if(!$chat->title)
                                                {{$chat->username}} id:({{$chat->id}})
                                                @else
                                            {{$chat->title}} id:({{$chat->id}})
                                                @endif
                                        </option>

                                    @endforeach

                                </select>
                            </div>

                        </div><!-- .panel -->
                        <button type="submit" class="btn btn-primary btn-lg">Large button</button>
                    </div>

                </div>
        </form>
    </div>
@endsection
