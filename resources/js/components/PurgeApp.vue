<template>
  <div>
    <button type="button" class="btn btn-primary" v-on:click="showModal">Purge</button>
    <purge-modal v-if="params.modal"
      v-on:close="params.modal = false"
      v-bind:params="params"
      v-on:request_purge="purge"
      v-bind:service="service"
      v-bind:account="account">
    </purge-modal>
  </div>
</template>

<script>
import PurgeModal from './purge-modal.vue';

export default {
  name: 'PurgeApp',
  components: { PurgeModal },
  props: {
    service: { type: String, default: '' },
    account: { type: String, default: '' },
    defaults: { type: Array, default: () => [] },
  },
  emits: ['purge'],
  data() {
    return {
      params: {
        modal: false,
        selected_default: this.defaults.length > 0 ? this.defaults[0].value : '',
        defaults: this.defaults,
      },
    };
  },
  methods: {
    showModal() {
      this.params.modal = true;
    },
    purge() {
      const urls = document.getElementById('urls')?.value || '';
      const data = new FormData();
      data.append('service', this.service);
      data.append('account', this.account);
      data.append('selected_default', this.params.selected_default);
      data.append('urls', urls);
      this.params.modal = false;
      this.$emit('purge', data);
    },
  },
};
</script>