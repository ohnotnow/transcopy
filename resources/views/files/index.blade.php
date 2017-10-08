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

<table class="table is-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Size</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($files as $file)
            <tr>
                <td>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="copies[{{ $file->id }}]" value="{{ $file->id }}">
                            {{ $file->basename }}
                            @if ($file->isDirectory())
                                /
                            @endif
                        </label>
                    </div>
                </td>
                <td>{{ $file->size }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</form>

@endsection
