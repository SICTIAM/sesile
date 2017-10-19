import React, { Component } from 'react'
import {Route, Switch} from 'react-router-dom'
import DashBoard from '../_components/DashBoard'
import ClasseursList from '../classeur/ClasseursList'
import ClasseursValid from '../classeur/ClasseursValid'
import ClasseursRemove from '../classeur/ClasseursRemove'
import Classeur from '../classeur/Classeur'
import CircuitsValidation from '../admin/CircuitsValidation'
import CircuitValidation from '../admin/CircuitValidation'
import ClasseursRetract from "../classeur/ClasseursRetract"
import Account from "../user/Account"
import Certificate from '../user/Certificate'
import HelpBoard from '../documentation/HelpBoard'
import Groups from '../admin/Groups'
import Group from '../admin/Group'
import Users from '../admin/Users'
import User from '../admin/User'
import UserListClasseurs from '../admin/UserListClasseurs'
import Types from '../admin/Types'
import Collectivites from '../admin/Collectivites'
import Collectivite from '../admin/Collectivite'
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
                <Route path={"/tableau-de-bord"} exact={true} component={DashBoard} />
                <Route path={"/utilisateur/mon-compte"} exact={true} component={Account} />
                <Route path={"/utilisateur/certificat-electronique"} exact={true} component={Certificate} />
                <Route path={"/classeurs/liste"} exact={true} component={ClasseursList} />
                <Route path={"/classeurs/valides"} exact={true} component={ClasseursValid} />
                <Route path={"/classeurs/retractables"} exact={true} component={ClasseursRetract} />
                <Route path={"/classeurs/supprimes"} exact={true} component={ClasseursRemove} />
                <Route path={"/documentations"} exact={true} component={HelpBoard} />
                <Route exact={true} path={"/classeur/:classeurId"} render={({ match }) => (
                    <Classeur classeurId={match.params.classeurId} />
                )} />
                <AdminRoute exact={true} path={"/admin/circuits-de-validation"} component={CircuitsValidation} user={user} />
                <AdminRoute exact={true} path={"/admin/groupes"} component={Groups} user={user} />
                <AdminRoute exact={true} path={"/admin/utilisateurs"} component={Users} user={user} />
                <AdminRoute exact={true} path={"/admin/collectivites"} component={Collectivites} user={user} />
                <AdminRoute exact={true} path={"/admin/types-classeur"} component={Types} user={user} />
                <Route exact={true} path={"/admin/:collectiviteId/groupe/:groupId?"} render={({ match}) => (
                    <AdminRoute exact={true} path={match.path} component={Group} user={user} match={match} />                    
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/utilisateur/:userId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={User} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/classeurs/:userId"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={UserListClasseurs} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/circuit-de-validation/:circuitId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={CircuitValidation} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/collectivite/:collectiviteId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={Collectivite} user={user} match={match} />
                )} />
            </Switch>
        )
    }
}

export default AppRoute