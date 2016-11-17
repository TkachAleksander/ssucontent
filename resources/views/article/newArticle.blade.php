@extends('layouts.app')
@section('content')


<div class="container">
	<div class="panel-group" id="accordion">
    @foreach($forms as $form)
        <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                    {{ $form->name }}
                </a>
           </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse ">
            <div class="panel-body">
                <form class="form-horizontal" action="/newArticle" method="POST">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-sm-12">
                    @foreach($setForms as $setForm)
                        <?php 
                        switch($setForm->el_name){
                            case 'input':
                                echo '<div class="row">'.
                                     '<div class="col-sm-'.$setForm->width.'">'.
                                     '<p><input type="text" class="form-control" name="title" placeholder="'.$setForm->el_label.'" required></p>'.
                                     '</div></div>';
                            break;
                            case 'textarea':
                                echo '<div class="row">'.
                                     '<div class="col-sm-'.$setForm->width.'">'.
                                     '<p><textarea class="form-control" rows="3" name="content" placeholder="Текст статьи" required>'.$setForm->el_label.'</textarea></p>'.
                                     '</div></div>';
                            break;

                        }
                        ?>
                    @endforeach
                    </div>

                	<div class="col-lg-12 pull-left">
                		<button class="btn btn-sm btn-primary" type="submit">Отправить</button>
                	</div> 
                </div>
                </form>
            </div>
        </div>
    @endforeach
    </div>

    <!-- <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                    Стандартная #2
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse">
            <div class="panel-body">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
            </div>
        </div>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
                    Стандартная #3
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse">
            <div class="panel-body">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
            </div>
        </div>
    </div>

</div> -->
@endsection