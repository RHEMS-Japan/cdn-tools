<template>
  <div>
    <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container">
          <div class="modal-header">
            <h3>
              Purge warning :  {{ service }} - {{ account }}
            </h3>
          </div>
          <div class="modal-body">
            <div>Distribution ID : {{ params.selected_default }}</div>
          </div>
          <div class="modal-footer">
            Are you sure?&nbsp;
            <button type="button" class="btn-primary" v-on:click="$emit('request_purge')">OK</button>
            <button type="button" class="btn-warning" v-on:click="$emit('close')">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      params: Object,
      service:  String,
      account:  String
    }
  }
</script>
