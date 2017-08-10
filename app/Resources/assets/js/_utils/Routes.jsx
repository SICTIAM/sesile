import React, { Component } from 'react'
import {Route, Switch} from 'react-router-dom'
import DashBoard from '../_components/DashBoard'
import ListClasseurs from '../classeur/ListClasseurs'
import Classeur from '../classeur/Classeur'
import Home from '../Home'

class AppRoute extends Component {
    render () {

        return (
            <Switch>
                <Route path={"/"} exact={true} component={Home} />
                <Route path={"/dashboard"} exact={true} component={DashBoard} />
                <Route path={"/classeur/list"} exact={true} component={ListClasseurs} />
                <Route exact={true} path={"/classeur/:classeurId"} render={({ match }) => (
                    <Classeur classeurId={match.params.classeurId} />
                )} />
            </Switch>
        )
    }
}

export default AppRoute;