import React, { Component } from 'react'
import {Route, Switch} from 'react-router-dom'
import DashBoard from '../_components/DashBoard'
import ListClasseurs from '../classeur/ListClasseurs'
import Classeur from '../classeur/Classeur'
import CircuitValidation from '../admin/CircuitValidation'
import Groups from '../admin/Groups'
import Group from '../admin/Group'
import AdminRoute from './AdminRoute'
import Home from '../Home'


class AppRoute extends Component {
    state = {
        user: null
    }
    componentDidMount() {
        fetch(Routing.generate('sesile_user_userapi_getcurrent'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => this.setState({user : json}))
    }
    render () {
        const user = this.state.user
        return (
            (!!user) &&
            <Switch>
                <Route path={"/"} exact={true} component={Home} />
                <Route path={"/dashboard"} exact={true} component={DashBoard} />
                <Route path={"/classeur/list"} exact={true} component={ListClasseurs} />
                <Route exact={true} path={"/classeur/:classeurId"} render={({ match }) => (
                    <Classeur classeurId={match.params.classeurId} />
                )} />
                <Route exact={true} path={"/admin/circuit-de-validation"} component={CircuitValidation} />
                <AdminRoute exact={true} path={"/admin/groupes"} component={Groups} user={user} />
                <Route exact={true} path={"/admin/:collectiviteId/groupe/:groupId?"} render={({ match}) => (
                    <AdminRoute exact={true} path={match.path} component={Group} user={user} match={match} />                    
                )} />
            </Switch>
        )
    }
}

export default AppRoute