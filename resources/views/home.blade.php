@extends('layout')

@section('contents')

<form method="POST" action="{{ route('copy.store') }}">
{{ csrf_field() }}
<h3 class="title is-3">
    Current Files
    <button class="button is-success">
        <span class="icon">
            <i class="fa fa-download"></i>
        </span>
    </button>
    <a href="{{ route('refresh') }}" class="button is-warning">
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
            <th>Copy?</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($files as $file)
            <tr>
                <td>
                    {{ $file->basename }}
                    @if ($file->isDirectory())
                        /
                    @endif
                </td>
                <td>{{ $file->size }}</td>
                <td>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="copies[{{ $file->id }}]" value="{{ $file->id }}">
                        </label>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</form>

@endsection
