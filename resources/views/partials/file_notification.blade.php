@if (session('fileList'))
    <nav class="breadcrumb has-bullet-separator" aria-label="breadcrumbs">
      <ul>
        <li><a href="#">Now Copying :</a></li>
        @foreach (session('fileList') as $file)
            <li><a href="#">{{ $file }}</a></li>
        @endforeach
      </ul>
    </nav>
@endif