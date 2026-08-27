<div class="card max-w-5xl mx-auto"><div class="card-body">
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
@csrf @if($method !== 'POST') @method($method) @endif
<div><label class="label">Title</label><input class="input" name="title" required value="{{ old('title',$post?->title) }}" placeholder="Write a clear, engaging title"></div>
<div><label class="label">Featured image</label><input class="input" type="file" name="featured_image" accept="image/*"></div>
<div><label class="label">Category</label><select class="input" name="category_id"><option value="">No category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post?->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div><label class="label">Excerpt</label><textarea class="input" name="excerpt" rows="3" placeholder="Short summary">{{ old('excerpt',$post?->excerpt) }}</textarea></div>
<div><label class="label">Article content</label><textarea class="input min-h-[320px]" name="content" required placeholder="Write your article here...">{{ old('content',$post?->content) }}</textarea><p class="text-xs text-slate-400 mt-1">Plain text/HTML-safe content is supported by the existing blog renderer.</p></div>
<div class="grid md:grid-cols-2 gap-4"><div><label class="label">Tags</label><input class="input" name="tags" value="{{ old('tags',$post?->tags) }}" placeholder="social media, earning, business"></div><div><label class="label">Meta title</label><input class="input" name="meta_title" value="{{ old('meta_title',$post?->meta_title) }}"></div></div>
<div><label class="label">Meta description</label><textarea class="input" name="meta_description" rows="2">{{ old('meta_description',$post?->meta_description) }}</textarea></div>
<div><label class="label">Publishing</label><select class="input" name="status"><option value="draft" @selected(old('status',$post?->status)==='draft')>Save draft</option><option value="published" @selected(old('status',$post?->status)==='published')>Publish now</option></select></div>
<div class="flex justify-end gap-3"><a href="{{ route('user.blog.index') }}" class="btn btn-outline">Cancel</a><button class="btn btn-primary"><x-icon name="blog" class="w-4 h-4"/> Save article</button></div>
</form></div></div>
