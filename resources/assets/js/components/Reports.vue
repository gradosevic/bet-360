<template>
    <div class="col-md-12">
    <div class="card">
        <div class="card-header">Reports</div>
        <div class="card-body">
            <div class="filters">
                <label for="from">From:</label>
                <input type="text" id="from" class="form-control">
                <label for="to">To:</label>
                <input type="text" id="to" class="form-control">
                <br/>
                <button type="submit" class="btn btn-primary" @click="go">Go</button>
            </div>
            <br/>
            <div v-if="reports && !loading" class="reports">
                <span v-if="!reports.length">No reports found.</span>
                <table class="table" v-if="reports.length">
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
            <div v-if="loading">Loading...</div>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                reports: null,
                loading: false
            };
        },
        methods:{
          country(code){
                return window.bet360.countries[code];
            },
            formatPrice(value) {
                let val = (value/1).toFixed(2).replace('.', ',')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            },
            go(){
                this.loading = true;
                window.axios.post('/api/reports', {
                    from: $( "#from").val(),
                    to: $( "#to").val()
                })
                .then((response) => {
                    //console.log(response.data);
                    this.reports = response.data;
                    this.loading = false;
                });
            }
        },
        mounted()
        {
            $( function() {
                $( "#from" ).datepicker({ dateFormat: 'yy-mm-dd' });
                $( "#to" ).datepicker({ dateFormat: 'yy-mm-dd' });
            } );
        }
    }
</script>