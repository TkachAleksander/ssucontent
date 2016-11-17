<?php

Route::auth();

// Вывод статей
Route::get('/', 'ArticleController@index');
Route::get('/showArticle/{id}', 'ArticleController@showArticle');


// Проверяет авторизован ли пользователь
Route::group(['middleware' => 'auth'], function(){  

    // Управление пользователями 
	Route::get('/registration', 'UserControlController@index');
	Route::post('/registration', 'UserControlController@registration');

    Route::get('/removeUser/{id}', 'UserControlController@removeUser');


    // Управление статьями
    Route::get('/newArticle', 'ArticleController@newArticle');
    Route::post('/newArticle', 'ArticleController@addNewArticle');
    
    Route::get('/editArticle/{id}', 'ArticleController@editArticle');
    
    Route::get('/removeArticle/{id}', 'ArticleController@removeArticle');
    Route::post('/updateArticle', 'ArticleController@updateArticle');
    
    
    // Управление формами и элементами 
    Route::get('/formControl', 'FormControlController@formControl');

    Route::post('/addTypeElement', 'FormControlController@addTypeElementToServer');
    Route::get('/removeTypeElement/{id}', 'FormControlController@removeTypeElementToServer');

    Route::post('/addLabelElement', 'FormControlController@addLabelElementToServer');
    Route::get('/removeLableElement/{id}', 'FormControlController@removeLableElementToServer');
    
    Route::post('/addElement', 'FormControlController@addElementToServer');
    Route::get('/removeElement/{id}', 'FormControlController@removeElementToServer');
    
    Route::post('/addForm', 'FormControlController@addFormToServer');
    Route::get('/removeForm/{id}', 'FormControlController@removeFormToServer');
    
    Route::post('/addSubElement', 'FormControlController@addSubElementToServer');
    Route::get('/removeSubElement/{id}', 'FormControlController@removeSubElementToServer');
    
    Route::get('/setForm', 'FormControlController@setForm');
    Route::post('/setForm', 'FormControlController@addSetFormToServer');
    
     
    // Управление сообщениями внутри системы 
    Route::get('/contactsMessages', 'MessangerController@index');
    Route::get('/showMessages/{name}', 'MessangerController@showMessages');
    Route::post('/sendMessages', 'MessangerController@sendMessages');


    // Конструктор форм
    Route::get('/constructorForm', 'ConstructorFormController@constructorForm');
    Route::post('/getSetElements', 'ConstructorFormController@getSetElements');
    Route::get('/newElement', 'ConstructorFormController@newElement');
    Route::post('/addNewElement', 'ConstructorFormController@addNewElementToServer');
    Route::post('/addSetFormsElements', 'ConstructorFormController@addSetFormsElementsToServer');

});