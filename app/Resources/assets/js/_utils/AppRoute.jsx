import React, { Component } from 'react'
import {Route, Switch} from 'react-router-dom'
import { object } from 'prop-types'

import AdminRoute from './AdminRoute'
import AdminClasseur from '../admin/AdminClasseur'
import AdminDashboard from '../admin/AdminDashboard'
import Emailing from '../admin/Emailing'
import Collectivites from '../admin/Collectivites'
import Collectivite from '../admin/Collectivite'
import CircuitsValidation from '../admin/CircuitsValidation'
import CircuitValidation from '../admin/CircuitValidation'
import Documentations from '../admin/Documentations'
import Documentation from '../admin/Documentation'
import Groups from '../admin/Groups'
import Group from '../admin/Group'
import Migration from '../admin/Migration'
import Notes from '../admin/Notes'
import Note from '../admin/Note'
import Users from '../admin/Users'
import User from '../admin/User'
import UserListClasseurs from '../admin/UserListClasseurs'
import UsersOzwillo from '../admin/UsersOzwillo'
import Types from '../admin/Types'

import Account from "../user/Account"
import Certificate from '../user/Certificate'
import Classeur from '../classeur/Classeur'
import ClasseursList from '../classeur/ClasseursList'
import ClasseursRemove from '../classeur/ClasseursRemove'
import ClasseursRetract from '../classeur/ClasseursRetract'
import ClasseursValid from '../classeur/ClasseursValid'
import ClasseurNew from '../classeur/ClasseurNew'
import ClasseursPreview from '../classeur/ClasseursPreview'
import DashBoard from '../Dashboard/DashBoard'
import Stats from '../Dashboard/Stats'
import HelpBoard from '../documentation/HelpBoard'


class AppRoute extends Component {

    static propTypes = {
        user: object.isRequired
    }

    render () {
        const { user, updateUserInfos } = this.props
        return (
            (!!user) &&
            <Switch>
                <Route path={"/"} exact={true} component={DashBoard} />
                <Route path={"/tableau-de-bord"} exact={true} component={DashBoard} />
                <Route path={"/utilisateur/mon-compte"} exact={true} component={() => <Account updateUserInfos={updateUserInfos}/>} />
                <Route path={"/utilisateur/certificat-electronique"} exact={true} component={Certificate} />
                <Route path={"/classeur/nouveau"} exact={true} component={ClasseurNew} />
                <Route path={"/classeurs/liste"} exact={true} component={ClasseursList} />
                <Route path={"/classeurs/valides"} exact={true} component={ClasseursValid} />
                <Route path={"/classeurs/retractables"} exact={true} component={ClasseursRetract} />
                <Route path={"/classeurs/supprimes"} exact={true} component={ClasseursRemove} />
                <Route path={"/classeurs/previsualisation"} exact={true} component={ClasseursPreview} />
                <Route path={"/documentations"} exact={true} component={HelpBoard} />
                <Route path={"/tableau-de-bord/stats"} exact={true} component={() => <Stats user={user}/>} />
                <Route exact={true} path={"/classeur/:classeurId"} render={({ match }) => (
                    <Classeur classeurId={match.params.classeurId} user={user} />
                )} />
                <AdminRoute exact={true} path={"/admin/tableau-de-bord"} component={AdminDashboard} user={user} />
                <AdminRoute exact={true} path={"/admin/circuits-de-validation"} component={CircuitsValidation} user={user} />
                <AdminRoute exact={true} path={"/admin/groupes"} component={Groups} user={user} />
                <AdminRoute exact={true} path={"/admin/utilisateurs"} component={Users} user={user} />
                <AdminRoute exact={true} path={"/admin/collectivites"} component={Collectivites} user={user} />
                <AdminRoute exact={true} path={"/admin/types-classeur"} component={Types} user={user} />
                <AdminRoute exact={true} path={"/admin/documentations"} component={Documentations} user={user} superAdmin={true} />
                <AdminRoute exact={true} path={"/admin/emailing"} component={Emailing} user={user} superAdmin={true} />
                <AdminRoute exact={true} path={"/admin/notes"} component={Notes} user={user} superAdmin={true} />
                <AdminRoute exact={true} path={"/admin/migration"} component={Migration} user={user} superAdmin={true} />
                <Route exact={true} path={"/admin/:collectiviteId/groupe/:groupId?"} render={({ match}) => (
                    <AdminRoute exact={true} path={match.path} component={Group} user={user} match={match} />                    
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/utilisateur/:userId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={User} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/collectivite/:collectiviteId?/utilisateurs-ozwillo"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={UsersOzwillo} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/classeurs/:userId"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={UserListClasseurs} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/classeur/:classeurId"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={AdminClasseur} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/:collectiviteId/circuit-de-validation/:circuitId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={CircuitValidation} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/collectivite/:collectiviteId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={Collectivite} user={user} match={match} />
                )} />
                <Route exact={true} path={"/admin/note/:noteId?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={Note} user={user} match={match} superAdmin={true} />
                )} />
                <Route exact={true} path={"/admin/documentation/:type?/:id?"} render={({match}) => (
                    <AdminRoute exact={true} path={match.path} component={Documentation} user={user} match={match} superAdmin={true} />
                )} />
            </Switch>
        )
    }
}

export default AppRoute

//TODO Add condition to non authenticated user
const AuthRoute = ({component: Component, ...rest}, { isLoggedIn }) => (
    <Route {...rest} render={(props) =>
        {
            <Component {...props} {...props.match.params}/>
        }
    }/>
)