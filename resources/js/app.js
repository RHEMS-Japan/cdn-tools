require('./bootstrap');
window.Vue = require('vue');
Vue.prototype.$http = axios;

//import components
import purge_modal from './components/purge-modal.vue';
Vue.component('purge-modal', purge_modal);

//params for Vue "#cloudfront"
const defaults_obj = document.getElementById('defaults');
const defaults_ary = defaults_obj.defaultValue.slice(0, -1).split(",");
var defaults = [];
for (var i=0; i<defaults_ary.length; i++) {
  var defaults_new_ary = { text: defaults_ary[i], value: defaults_ary[i]};
  defaults.push(defaults_new_ary);
}
const service_obj = document.getElementById('service');
const account_obj = document.getElementById('account');
const service_name = service_obj.defaultValue.slice(0, -1);
const account_name = account_obj.defaultValue.slice(0, -1);

new Vue({
  el: '#purge',
  data: {
    params: {
      defaults: defaults,
      selected_default: defaults[0]['value'],
      modal: false
    }
  },
  methods: {
    purge() {   
      var self = this;
      var urls = document.getElementById('urls').value;
      var params = this.params; 
      params['urls'] = urls;
      params['service'] = service_name;
      params['account'] = account_name;
      
      this.$http.post('/ajax/purge', params)
        .then(function(response){
          console.log(response); 
          update_queue(self);
          alert('送信が完了しました。');
          params['modal'] = false;
        }).catch(function(error){
          alert('config/cdn.phpの設定が間違っています。');
      });
    }
  }
});

new Vue({
  el: '#update_queue',
  methods: {
    check_queue() {
      var self = this;
      update_queue(self);
    }
  }
});

async function update_queue(self)
{
  const params = {
    "service" : service_name,
    "account" : account_name,
  };
  self.$http.post('/ajax/update', params)
    .then(async function(response){
      console.log(response);

      var queue = await document.getElementsByClassName('queue');
      var historys = await response['data']['historys'];
      console.log("queue");
      console.log(queue);
      console.log("historys");
      console.log(historys);
      
      if (queue.length == historys.length) {
        await rewrite_dom_queue(queue, historys);
      } else {
        var defference = historys.length - queue.length;
        var old_historys = historys.slice(defference, historys.length);
        var new_historys = historys.slice(0, defference);
        if (queue.length == 0) {
          await insert_dom_queue(new_historys);
        } else {
          await rewrite_dom_queue(queue, old_historys);
          await insert_dom_queue(new_historys);
        }
      }
      console.log("queue was updated.");
    }).catch(function(error){
      alert('config/cdn.phpの設定が間違っています。');
      console.log("ajax error!");
  })
}

async function rewrite_dom_queue(queue, historys)
{
  for (var i=0; i<=queue.length-1; i++) {
    var countor = i.toString();
    var state = historys[countor]['done'] ? "Done" : "Processing";
    queue[countor]['innerHTML'] = "<td>" + historys[countor]['updated_at'] + "</td><td>" + historys[countor]['purgeId'] + "</td><td>" + state + "</td>";
  }
}

async function insert_dom_queue(historys) {
  for (var i=historys.length-1; i>=0; i--) {
    var countor = i.toString();
    var state = historys[countor]['done'] ? "Done" : "Processing";
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    td1.textContent = historys[countor]['updated_at'];
    td2.textContent = historys[countor]['purgeId'];
    td3.textContent = state;
    var tr = document.createElement('tr');
    tr.setAttribute('class', 'queue');
    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
    var target = document.getElementsByClassName('queue');
    if (target.length == 0) {
      var target_parent = document.getElementById('queue-body');
      console.log(target_parent);
      target_parent.appendChild(tr); 
      console.log("aaa");
    } else {
      target[0].parentElement.insertBefore(tr, target[0]);
      if (target.length > 5) {
        target[0].parentElement.removeChild(target[target.length -1]);
      }
    }
  }
}

