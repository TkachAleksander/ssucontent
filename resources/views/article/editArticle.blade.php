@extends('layouts.app')
@section('content') 

    <div class="container table-bordered container-edit" style="padding: 15px;">
        <form class="form-horizontal" action="/updateArticle" method="POST">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-lg-6"><p><input type="text" class="form-control" name="title" placeholder="Заголовок" value="{{ $article->title }}" required></p></div>
            <div class="col-lg-12"><p><textarea class="form-control" rows="3" name="content" placeholder="Текст статьи" required>{{ $article->content }}</textarea></p></div>

            <div class="form-group">
  	        	<label for="inputName" class="col-sm-1 control-label">Автор</label>
  	        	<div class="col-sm-3">
  	        	    <input type="text" class="form-control text-center" id="inputName" name="author" value="{{ $article->author }}" readonly required>
  	        	</div>
              <?php $role = Auth::user()->role; ?>
              @if ($role == 'admin')
              <label for="inputEditReason" class="col-sm-2 control-label">Причина ред-ния:</label>
              <div class="col-sm-5">
                  <input type="text" class="form-control text-center" id="inputEditReason" name="edit_reason" value="Не указана" required>
              </div>
              @endif
  	        </div>         

  	        <input type="hidden" name="id" value="{{ $article->id}}"  required>

    		<div class="col-lg-12 pull-left">
    			<button class="btn btn-sm btn-primary" type="submit">Редактировать</button>
    		</div>

    	</div>
        </form>
    </div>

@endsection