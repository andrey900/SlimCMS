$(function() {

    $('#side-menu').metisMenu();

});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        topOffset = 50;
        width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    var element = $('ul.nav a').filter(function() {
        return this.href == url;// || url.href.indexOf(this.href) == 0;
    }).addClass('active').parent().parent().addClass('in').parent();
    if( element.length == 0 ){
        element = $('ul.nav a').filter(function() {
            return url.href.indexOf(this.href) == 0;
        }).addClass('active').parent().parent().addClass('in').parent();
    }
    if (element.is('li')) {
        element.addClass('active');
    }
});

var Admin = {
    Panel: {
        Fields: {
            JsonMylti: {
                init: function(){},
                initEvents: function(){},
                addValue: function(){},
                removeValue: function(){},
                reCalculateValue: function(){},
                jsonParseFields: function(){},
            },
            uploadFiles: {
                init: function(){}
            },
            mediaLibrary: {
                init: function(){}
            }
        },
        Elements: {
            CountPage: {
                init: function(){}
            }
        },
        Configurator: {
            init: function(){},
            sortable: function(){},
        }
    }
}

Admin.Panel.Configurator = {
    field: false,

    init: function(){
        this.sortable();
        this.events();
    },
    sortable: function(){
            $( "#sortable1, #sortable2, #sortable3, #sortable4" ).sortable({
              connectWith: ".connectedSortable",
              cancel: ".ui-state-disabled"
            }).disableSelection();
    },
    events: function(){
        var self = this;
        var renameCounter = 0;
        $(document).on('click', '.save-config', function(){
            var show = $( "#sortable2" ).sortable( "serialize", { key: "show[]", expression: new RegExp(/field-(.+)/) } );
                //hide = $( "#sortable1" ).sortable( "serialize", { key: "hide[]" } );
            $.ajax({
                method: 'OPTIONS',
                url: '/ajax?'+show+'&'+$(this).closest('form').serialize(),
                dataType: "json",
                cache: false
            }).success(function(data){
                if( data.success )
                    location.reload();
            });
        });
        $(document).on('click', '.move-to-show', function(){
            $('#sortable1 p').not('.ui-state-disabled').each(function(index, element) {
                $(element).appendTo($('#sortable2'));
            });
            $('#sortable3 p').not('.ui-state-disabled').each(function(index, element) {
                $(element).appendTo($('#sortable4'));
            });
        });
        $(document).on('click', '.move-to-hide', function(){
            $('#sortable2 p').not('.ui-state-disabled').each(function(index, element) {
                $(element).appendTo($('#sortable1'));
            });
            $('#sortable4 p').not('.ui-state-disabled').each(function(index, element) {
                $(element).appendTo($('#sortable3'));
            });
        });
        /*$(document).on('dblclick', '#sortable2 p', function(){
            var text = $(this).text();
            $(this).addClass('field-rename-'+renameCounter).hide();
            var field = '<div>'+
                '<input name="field-rename-'+renameCounter+'" type="text" value="'+text+'">'+
                '<a href="javascript:void(0);">Y</a>'+
                '<a href="javascript:void(0);">N</a>'+
                '</div>';
            $(this).after($(field));
        });*/
        $(document).on('dblclick', '#sortable2 p, #sortable4 p', function() {
            $('.modal-set-value .old-value').val($(this).text());
            self.field = $(this);
            $('.modal-set-value').modal('show');
        });
        $(document).on('click', ".set-label-field", function () {
            var fNV = $(this).closest(".modal-body").find('.new-value');
            if(fNV.val().length > 0){
                self.field.text(fNV.val());
                fNV.val("");
                self.field = false;
                $('.modal-set-value').modal('hide');
            } else {
                fNV.addClass("danger");
            }
        })
    }
}

Admin.Panel.Elements.CountPage = {
    init: function(){
        $('form.ajax-form').on('change', '.ajax-submit', function(){
            $(this).closest('form').submit();
        });
    }
}

Admin.Panel.Fields.JsonMylti = {
    fieldMultiName: "",
    init: function(){
        var informInput = $(document).find('.json-multiply .disabled-jsonmulti.jsonmultivalue');
        var initHiddenInput = $('<input/>');
            initHiddenInput.addClass('jsonmultivalue');
            initHiddenInput.prop('type', 'hidden');
            initHiddenInput.attr('name', informInput.attr('name'));
            initHiddenInput.val(informInput.val());
        informInput.after(initHiddenInput);

        this.setFieldName(informInput);

        if( informInput.val() )
            this.jsonParseFields(informInput);

        this.initEvents();
    },
    addValue: function(e, value){
        var $e = $(e);
        var btnRemove  = $('<a class="input-group-addon remove btn btn-primary" href="javascript:void(0);">-</a>');
        var inputValue = $('<input class="form-control" name="'+this.fieldMultiName+'" type="text">').val(value);
        var blockGroup = $('<div></div>').addClass('form-group').addClass('input-group').append(btnRemove).append(inputValue);
        $e.closest('.json-multiply').append(blockGroup);
    },
    setFieldName: function($e){
        this.fieldMultiName = $e.attr('name')+"_";
    },
    removeValue: function(e){
        var $e = $(e);
        $e.closest('.form-group').remove();
    },
    initEvents: function(){
        var self = this;
        $(document).on('click', '.json-multiply a.input-group-addon.add', function(e){
            self.addValue(this);
            e.preventDefault();
        });
        $(document).on('click', '.json-multiply a.input-group-addon.remove', function(e){
            self.removeValue(this);
            self.reCalculateValue();
            e.preventDefault();
        });
        $(document).on('focusout', '.json-multiply input.form-control', function(e){
            self.reCalculateValue();
            e.preventDefault();
        });
    },
    reCalculateValue: function(){
        var features = {};
        $('.json-multiply input.form-control[name="'+this.fieldMultiName+'"]').each(function(){
            var val = $(this).val();
            if( val )
            features[val] = val;
        });
        var json = JSON.stringify(features);
        $('.jsonmultivalue').val(json);
    },
    jsonParseFields: function($e){
        var self = this;
        var data = JSON.parse($e.val());
        this.setFieldName($e);
        var item = document.querySelector('.json-multiply a.input-group-addon.add');
        $.each(data, function(i, e){
            self.addValue(item, e);
        });
    }
}

Admin.Panel.Fields.uploadFiles.init = function(){
    $('.file_upload').filer();
}

Admin.Panel.Fields.mediaLibrary.init = function(){
    $('.file_upload').filer({
        changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h3>Drag&Drop files here</h3> <span style="display:inline-block; margin: 15px 0">or</span></div><a class="jFiler-input-choose-btn blue">Browse Files</a></div></div>',
        showThumbs: true,
        theme: "dragdropbox",
        templates: {
            box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
            item: '<li class="jFiler-item">\
                    <div class="jFiler-item-container">\
                        <div class="jFiler-item-inner">\
                            <div class="jFiler-item-thumb">\
                                <div class="jFiler-item-status"></div>\
                                <div class="jFiler-item-info">\
                                    <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                    <span class="jFiler-item-others">{{fi-size2}}</span>\
                                </div>\
                                {{fi-image}}\
                            </div>\
                            <div class="jFiler-item-assets jFiler-row">\
                                <ul class="list-inline pull-left">\
                                    <li>{{fi-progressBar}}</li>\
                                </ul>\
                                <ul class="list-inline pull-right">\
                                    <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                </ul>\
                            </div>\
                        </div>\
                    </div>\
                </li>',
            itemAppend: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        <span class="jFiler-item-others">{{fi-size2}}</span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
            progressBar: '<div class="bar"></div>',
            itemAppendToEnd: false,
            removeConfirmation: true,
            _selectors: {
                list: '.jFiler-items-list',
                item: '.jFiler-item',
                progressBar: '.bar',
                remove: '.jFiler-item-trash-action'
            }
        },
        dragDrop: {
            dragEnter: null,
            dragLeave: null,
            drop: null,
        },
        uploadFile: {
            url: "./php/upload.php",
            data: null,
            type: 'POST',
            enctype: 'multipart/form-data',
            beforeSend: function(){},
            success: function(data, el){
                var parent = el.find(".jFiler-jProgressBar").parent();
                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                    $("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");
                });
            },
            error: function(el){
                var parent = el.find(".jFiler-jProgressBar").parent();
                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                    $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");
                });
            },
            statusCode: null,
            onProgress: null,
            onComplete: null
        }
    });
}

$(document).ready(function(){
    try{
        Admin.Panel.Fields.JsonMylti.init();
        Admin.Panel.Fields.uploadFiles.init();
        Admin.Panel.Elements.CountPage.init();
        Admin.Panel.Configurator.init();
    } catch(e){
        console.log("Error: "+ e.name);
    }
});