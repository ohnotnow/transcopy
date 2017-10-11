@extends('layouts.app')

@section('contents')

@include('partials.switch_category', ['text' => 'Torrents', 'route' => route('torrent.index')])

<form method="POST" action="{{ route('file.copy') }}">
{{ csrf_field() }}
<h3 class="title is-3">
    Current Files
    <button class="button is-success">
        <span class="icon">
            <i class="fa fa-download"></i>
        </span>
    </button>
    <a href="{{ route('file.refresh') }}" class="button is-warning">
        <span class="icon">
            <i class="fa fa-refresh"></i>
        </span>
    </a>
</h3>

@foreach ($files as $file)
    <div class="columns">
        <div class="column">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="copies[{{ $file->id }}]" value="{{ $file->id }}">
                    {{ $file->basename }}
                    @if ($file->isDirectory())
                        /
                    @endif
                    ({{ $file->size }})
                </label>
            </div>
        </div>
    </div>
@endforeach

</form>

@endsection
