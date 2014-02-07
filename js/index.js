/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var Config = {
    form:function() {
         if (document.forms.manager) return document.forms.manager;
         else return false;
    },
    cancel:function(name) {
        if (this.form()) this.form().action = name;
        this.form().submit();
    },        
    application:{
        parent:this,
        reset:function(name) {
            var form = this.parent.Config.form();
            if (form) form.application.value = name;
        },
        assign:function() {
            var form = this.parent.Config.form();
            if (form) {
                form.action = window.base_url + 'manager/application/assign';
                form.submit();
            }
        },
        select:function(name, submit) {
            var form = this.parent.Config.form();
            if (form) {
                if (form.application) form.application.value = name;
                this.parent.Config.environment.reset('');
                this.parent.Config.namespaces.reset('');
                if ( ! submit) form.submit();
            }
        },
        unassign:function(name) {
            var form = this.parent.Config.form();
            if (form && confirm('Are you sure?')) {
                if (form.application) form.application.value = name;
                this.parent.Config.environment.reset('');
                this.parent.Config.namespaces.reset('');
                form.action = window.base_url + 'manager/application/unassign';
                form.submit();
            }
        }
    },
    environment:{
        parent:this,
        reset:function(name) {
            var form = this.parent.Config.form();
            if (form) form.environment.value = name;
        },
        assign:function() {
            var form = this.parent.Config.form();
            if (form) {
                form.action = window.base_url + 'manager/environment/assign';
                form.submit();
            }
        },
        select:function(name, submit) {
            var form = this.parent.Config.form();
            if (form) {
                form.environment.value = name;
                this.parent.Config.namespaces.reset('');
                if ( ! submit) form.submit();
            }
        },
        unassign:function(name) {
            var form = this.parent.Config.form();
            if (form && confirm('Are you sure?')) {
                if (form.environment) form.environment.value = name;
                this.parent.Config.namespaces.reset('');
                form.action = window.base_url + 'manager/environment/unassign';
                form.submit();
            }
        }
    },
    namespaces:{
        parent:this,
        reset:function(name) {
            var form = this.parent.Config.form();
            if (form) form.namespaces.value = name;
        },
        assign:function() {
            var form = this.parent.Config.form();
            if (form) {
                form.action = window.base_url + 'manager/namespaces/create';
                form.submit();
            }
        },
        select:function(name, submit) {
            var form = this.parent.Config.form();
            if (form) {
                form.namespaces.value = name;
                if ( ! submit) form.submit();
            }
        },
        erase:function(name) {
            var form = this.parent.Config.form();
            if (form && confirm('Are you sure?')) {
                if (form.namespaces) form.namespaces.value = name;
                form.action = window.base_url + 'manager/namespaces/erase';
                form.submit();
            }
        }
    },
    content:{
        parent:this,
        update:function(index) {
            var form = this.parent.Config.form();
            if (form) {
                if (form.update) form.update.value = index;
                form.submit();
            }
            
        },
        erase:function(index) {
            var form = this.parent.Config.form();
            if (form && confirm('Are you sure?')) {
                if (form.remove) form.remove.value = index;
                form.submit();
            } 
        }
    }
}
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.search.slice(window.location.search.indexOf('?') + 1).split('&');
    for(i in hashes) {
        hash = hashes[i].split('=');
        vars[hash[0]] = decodeURIComponent(hash[1]);
    }
    return vars;
}
$(document).ready(function() {
    $_GET = getUrlVars();
    if ($_GET['application']) {
        Config.form().action = window.base_url + 'manager';
        if ($_GET['application']) Config.application.select($_GET['application'], true);
        if ($_GET['environment']) Config.environment.select($_GET['environment'], true);
        if ($_GET['namespaces']) Config.namespaces.select($_GET['namespaces'], true);
        Config.form().submit();
    }
    $('.select_value').each(function(index,element){
        $(element.options).each(function(option_index,option_element) {
            if (option_element.value == element.title) {
                element.selectedIndex = option_index;
            }
        });
    });
});