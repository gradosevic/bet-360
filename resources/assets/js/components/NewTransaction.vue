<template>
    <div class="col-md-6">
    <div class="card">
        <div class="card-header">New Transaction</div>
        <div class="card-body" v-if="!saving">
            <div v-if="!users">Loading...</div>
            <div v-if="error">
                <span style="color: red">{{error}}</span>
            </div>
            <div v-if="users">
                <form>
                    <div class="form-group">
                        <label for="selectUser">User</label>
                        <select id="selectUser" class="form-control" v-on:change="onChange" v-model="selectedUserId">
                            <option value="" selected="selected">-- Select user --</option>
                            <option v-for="u in users" v-bind:value="u.id">{{u.name}}</option>
                        </select>
                    </div>
                    <div v-if="selectedUserId">
                        <div class="form-group">
                            <label for="tType">Transaction type</label>
                            <select id="tType" v-model="type" class="form-control">
                                <option value="1">Deposit</option>
                                <option value="2">Withdrawal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount ($)</label>
                            <input type="number" min="0.00" max="10000000.00" step="0.01" class="form-control" id="amount" placeholder="Enter amount" v-model="amount">
                        </div>
                        <button type="submit" class="btn btn-primary" @click="onSubmit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body" v-if="saving">
            <span><i>Saving...</i></span>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                users: null,
                selectedUserId: null,
                saving: false,
                amount: null,
                type: 1,
                error: null
            };
        },
        methods:{
            onChange(e){
                this.user = this.users.find((user) => user.id == this.selectedUserId);
            },
            onSubmit(){
                this.error = null;
                this.saving = true;
                window.axios.post('/api/' + ((this.type == 1)?'deposit':'withdrawal'), {
                    user_id: this.selectedUserId,
                    amount: this.amount
                })
                .then((response) => {
                    this.saving = false;
                    this.selectedUserId = null
                    this.amount = null;
                })
                .catch((error) => {
                    console.log(error.response);
                    this.error = error.response.data.message;
                    this.saving = false;
                });
            },
            loadUsers(){
                this.error = null;
                window.axios.get('/api/users')
                .then((response) => {
                    console.log(response.data);
                    this.users = response.data;
                })
                .catch((error) => {
                    console.log(error.response);
                    this.error = error.response.data.message;
                });
            }
        },
        mounted()
        {
            this.loadUsers();
        }
    }
</script>