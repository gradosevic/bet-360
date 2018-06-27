<template>
    <div class="col-md-12">
        <div class="col-md-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" v-bind:class="{active: context == 'users'}" href="#users" @click="context = 'users'">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" v-bind:class="{active: context == 'new-transaction'}" href="#new-transaction" @click="context = 'new-transaction'">New Transaction</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" v-bind:class="{active: context == 'transactions'}" href="#transactions" @click="context = 'transactions'">Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" v-bind:class="{active: context == 'reports'}" href="#reports" @click="context = 'reports'">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" v-bind:class="{active: context == 'api'}" href="#api" @click="context = 'api'">API</a>
                </li>
            </ul>
        </div>
        <passport-personal-access-tokens v-if="context == 'api'"></passport-personal-access-tokens>
        <new-transaction v-if="context == 'new-transaction'"></new-transaction>
        <transactions v-if="context == 'transactions'"></transactions>
        <reports v-if="context == 'reports'"></reports>
        <users v-if="context == 'users'"></users>
    </div>
</template>
<script>
    export default {
        data()
        {
            return{
                context: 'users'
            }
        }
    }
</script>