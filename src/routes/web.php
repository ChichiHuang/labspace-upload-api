 <?php

Route::group(['prefix' => 'lab/api/upload','middleware' => ['cors']],function (){
	Route::get('labspace', function(){
	    return 'Hello Labspace package upload api';
	});

	Route::post('/image/ckeditor', 'Labspace\UploadApi\Controllers\ImageController@ckeditor'); 
    Route::post('/image/ckeditor-json', 'Labspace\UploadApi\Controllers\ImageController@ckeditorJson'); 
    Route::post('/video/ckeditor-json', 'Labspace\UploadApi\Controllers\VideoController@ckeditorJson'); 

    Route::group(['middleware' => ['jwt:member']], function() {
        Route::post('/image/base64', 'Labspace\UploadApi\Controllers\ImageController@base64Source'); 
        Route::post('/image/file', 'Labspace\UploadApi\Controllers\ImageController@fileSource'); 
        
        Route::post('/video/file', 'Labspace\UploadApi\Controllers\VideoController@fileSource'); 
    });


});
?>