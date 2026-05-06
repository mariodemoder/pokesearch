<template>
    <section class="mx-auto w-full max-w-3xl rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-orange-100 backdrop-blur sm:p-8">
        <header class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-orange-600">PokeAPI Demo</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Buscar Pokemon Benicio</h1>
            <p class="mt-2 text-sm text-slate-600">Consumo via API propia en Laravel con estados de carga, error y exito.</p>
        </header>

        <div class="mb-6 flex flex-col gap-3 sm:flex-row">
            <input
                v-model.trim="nombre"
                @keyup.enter="buscarPokemon"
                type="text"
                placeholder="pikachu"
                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
            />
            <button
                @click="buscarPokemon"
                :disabled="loading || !nombre"
                class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
            >
                Buscar
            </button>
        </div>

        <p v-if="loading" class="mb-4 animate-pulse rounded-lg bg-orange-50 px-4 py-3 text-orange-700">Cargando...</p>
        <p v-if="error" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-red-700">{{ error }}</p>

        <article v-if="pokemon" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <img
                    :src="pokemon.sprite"
                    :alt="pokemon.name"
                    class="h-32 w-32 rounded-full border border-slate-200 bg-white p-2"
                />

                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold capitalize text-slate-900">{{ pokemon.name }}</h2>
                    <p class="mt-2 text-slate-700">Altura: <strong>{{ pokemon.height }}</strong></p>
                    <p class="text-slate-700">Peso: <strong>{{ pokemon.weight }}</strong></p>
                </div>
            </div>
        </article>
    </section>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            nombre: '',
            pokemon: null,
            loading: false,
            error: null,
        };
    },
    methods: {
        async buscarPokemon() {
            if (!this.nombre) {
                return;
            }

            this.loading = true;
            this.error = null;
            this.pokemon = null;

            try {
                const normalizedName = this.nombre.toLowerCase();
                const res = await axios.get(`/api/pokemon/${normalizedName}`);
                this.pokemon = res.data;
            } catch (e) {
                const status = e?.response?.status;
                const backendMessage = e?.response?.data?.error;

                if (status === 404) {
                    this.error = backendMessage || 'Pokemon no encontrado';
                } else if (status === 422) {
                    this.error = backendMessage || 'Nombre invalido. Usa letras, numeros o guion.';
                } else if (status === 502 || status === 504) {
                    this.error = backendMessage || 'Servicio externo no disponible. Intenta nuevamente.';
                } else {
                    this.error = 'No se pudo completar la busqueda. Intenta nuevamente.';
                }
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
