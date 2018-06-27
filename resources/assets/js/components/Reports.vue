<template>
    <div class="col-md-12">
    <div class="card">
        <div class="card-header">Reports</div>
        <div class="card-body">
            <div class="filters">
                <label for="from">From:</label>
                <input v-model="filter.from" type="text" id="from">&nbsp;
                <label for="to">To:</label>
                <input v-model="filter.to" type="text" id="to">
            </div>
            <div v-if="reports" class="reports">
                <table class="table">
                    <thead>
                        <tr>
                            <th><abbr title="Date">Date</abbr></th>
                            <th><abbr title="Total Withdrawals">Total Withdrawals</abbr></th>
                            <th><abbr title="Total Deposits">Total Deposits</abbr></th>
                            <th><abbr title="Total Number of Withdrawals">Withdrawals</abbr></th>
                            <th><abbr title="Total Number of Deposits">Deposits</abbr></th>
                            <th><abbr title="Total Number of Users">Users</abbr></th>
                            <th><abbr title="Country">Country</abbr></th>
                        </tr>
                        </thead>
                    <tbody v-for="r in reports" >
                        <tr>
                            <td>{{r.date}}</td>
                            <td>${{formatPrice(r.total_withdrawals)}}</td>
                            <td>${{formatPrice(r.total_deposits)}}</td>
                            <td>{{r.withdrawals}}</td>
                            <td>{{r.deposits}}</td>
                            <td>{{r.users}}</td>
                            <td>{{country(r.country)}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="!reports">Loading...</div>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                reports: null,
                filter:{
                    from: null,
                    to: null
                }
            };
        },
        methods:{
          country(code){
                return window.bet360.countries[code];
            },
            formatPrice(value) {
                let val = (value/1).toFixed(2).replace('.', ',')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            }
        },
        created(){
        },
        mounted()
        {
            window.axios.post('/api/reports', {
                from: this.filter.from,
                to: this.filter.to
            })
            .then((response) => {
                console.log(response.data);
                this.reports = response.data;
            });
            $( function() {
                $( "#from" ).datepicker();
                $( "#to" ).datepicker();
            } );
        }
    }
</script>