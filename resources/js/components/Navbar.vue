<template>
  <nav class="navbar">
    <RouterLink class="navbar__brand" to="/">Vuesplash</RouterLink>
    <div class="navbar__menu">
      <div v-if="isLogin" class="navbar__item">
        <button class="button" @click="showForm = ! showForm">
          <i class="icon ion-md-add"></i>
          Submit a photo
        </button>
      </div>
      <span v-if="isLogin" class="navbar__item">
        <ul class="list" style="padding-left: 0px">
          <div @click="dropDownActive">{{ username }}</div>
          <li class="list-item" v-if="active" style="list-style: none">
            <RouterLink class="button" to="/photos/user/">{{ username }}の写真</RouterLink>
          </li>
        </ul>
      </span>
      <div v-else class="navbar__item">
        <RouterLink class="button button--link" to="/login">Login / Register</RouterLink>
      </div>
    </div>
    <PhotoForm v-model="showForm" />
  </nav>
</template>

<script>
import PhotoForm from "./PhotoForm.vue";

export default {
  components: {
    PhotoForm
  },
  data() {
    return {
      showForm: false,
      active: false
    };
  },
  computed: {
    isLogin() {
      return this.$store.getters["auth/check"];
    },
    username() {
      return this.$store.getters["auth/username"];
    }
  },
  methods: {
    dropDownActive: function() {
      if (this.active) {
        this.active = false;
      } else {
        this.active = true;
      }
    }
  }
};
</script>