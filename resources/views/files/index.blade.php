@extends('layouts.app')

@section('contents')

<form method="POST" action="{{ route('file.copy') }}">
{{ csrf_field() }}
<h3 class="text-xl shadow rounded p-4 bg-grey-lighter">
    <div class="inline-flex items-center">
        <div class="flex-1 mx-2">
            <button title="Download">
                @svg('zondicons/arrow-outline-down.svg', 'icon-button')
            </button>
        </div>
        <div class="flex-1 mx-2">
            <a href="{{ route('file.refresh') }}" title="Refresh list">
                @svg('zondicons/reload.svg', 'icon-button')
            </a>
        </div>
        <div class="flex-1 mx-2">
            <a href="{{ route('torrent.index') }}">Torrents</a>
        </div>
        <div class="flex-1">
            <span class="underline">Files</span>
        </div>
    </div>
</h3>

<div class="py-8 px-4 border-l-2">
    @foreach ($files as $file)
        <div class="mb-4">
            <label>
                <input type="checkbox" name="copies[{{ $file->id }}]" value="{{ $file->id }}">
                {{ $file->webFriendlyName() }}
                @if ($file->isDirectory())
                    /
                @endif
                <span class="opacity-50">({{ $file->formattedSize() }})</a>
            </label>
        </div>
    @endforeach
</div>

</form>

@endsection
