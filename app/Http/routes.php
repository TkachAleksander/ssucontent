<?php

Route::auth();

// Вывод форм
Route::get('/', 'HomeController@index');

// Проверяет авторизован ли пользователь
Route::group(['middleware' => 'auth'], function(){

    // Обмен сообщениями
    Route::post('/sendMessage', 'MessageController@sendMessage');
    Route::get('/viewForm/{id_forms_departments}', 'ViewFormController@viewForm');

    // Управление пользователями 
	Route::get('/registration', 'UserControlController@index');
	Route::post('/registration', 'UserControlController@registration');
    Route::get('/removeUser/{id}', 'UserControlController@removeUser');
    Route::get('/editUsers/{id}', 'UserControlController@editUsers');
    Route::post('/updateEditUsers/{id}', 'UserControlController@updateEditUsers');

    // Управление формамии homeUser
    Route::post('/submitFillForm', 'ViewFormController@submitFillForm');
    Route::post('/submitFillFormRepeatedly', 'ViewFormController@submitFillFormRepeatedly');

    // Управление формами homeAdmin
    Route::post('/rejectForm', 'ViewFormController@rejectForm');
    Route::post('/acceptForm', 'ViewFormController@acceptForm');

    // Управление завершенными формами
    Route::get('/doneForm', 'ViewFormController@doneForm');
    Route::get('/viewDoneForm/{id_forms_departments}', 'ViewFormController@viewDoneForm');


    // Конструктор форм
    //> Вкладка Собрать форму
    Route::get('/constructor/addForm', 'ConstructorFormController@addForm');
    Route::post('/constructor/getSetElements', 'ConstructorFormController@getSetElements');
    Route::post('/constructor/addNewForm', 'ConstructorFormController@addNewForm');
    Route::post('/constructor/editForm', 'ConstructorFormController@editForm');
    Route::post('/constructor/addEditedNewForm', 'ConstructorFormController@addEditedNewForm');
    Route::post('/constructor/reestablishForm', 'ConstructorFormController@reestablishForm');
    Route::post('/constructor/removeForms', 'ConstructorFormController@removeFormsToServer');
    //> Вкладка Добавить элемент
    Route::get('/constructor/newElement', 'ConstructorFormController@newElement');
    Route::post('/constructor/addNewElement', 'ConstructorFormController@addNewElementToServer');
    //// Кнопки редактирования set_elements (вызов из js)
    Route::post('/editSetElementFromForm', 'ConstructorFormController@editSetElementFromForm');
    Route::post('/addEditedNewSetElement', 'ConstructorFormController@addEditedNewSetElement');
    Route::post('/removeSetElement', 'ConstructorFormController@removeSetElement');
    //> Вкладка Просмотр списка форм
    Route::get('/constructor/showForms', 'ConstructorFormController@showForms');
    Route::get('/viewFormEmpty/{id_forms}', 'ConstructorFormController@viewFormEmpty');
    //> Вкладка Связи
    Route::get('/constructor/formsConnectUsers', 'ConstructorFormController@formsConnectUsers');
    Route::post('/constructor/getTableConnectUsers', 'ConstructorFormController@getTableConnectUsers');
    Route::post('/constructor/setTableConnectUsers', 'ConstructorFormController@setTableConnectUsers');
    Route::post('/constructor/setTableDisconnectUsers', 'ConstructorFormController@setTableDisconnectUsers');
    //> Вкладка Отделы
    Route::get('/constructor/departments', 'ConstructorFormController@getAllDepartments');
    Route::post('/constructor/setDepartments', 'ConstructorFormController@setDepartments');
    Route::post('/constructor/removeDepartments', 'ConstructorFormController@removeDepartments');
    Route::post('/constructor/reestablishDepartments', 'ConstructorFormController@reestablishDepartments');
    Route::post('/constructor/editDepartments', 'ConstructorFormController@editDepartments');

});