'use strict';

import React, { Component } from 'react';
import {BrowserRouter as Router, Route, Link} from 'react-router-dom'
import Login from './_components/Login'
import AppRoute from "./_utils/Routes";

class App extends Component {

    constructor(props) {
        super(props);
        this.state = {user: {}};
    }

    componentWillMount() {
        fetch(Routing.generate('isauthenticated_user_api'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(json => this.setState({user : json}))
    }

    componentDidMount() {
        $(document).foundation();
    }

    render () {
        return (
        <div className="grid-x grid-y medium-grid-frame grid-frame">
            <div className="cell header medium-cell-block-container cell-block-container">
                <div className="grid-x align-middle align-center">
                    <div className="medium-1 cell">
                        <div className="logo text-center">
                            <Link to={'/dashboard'}>
                                <div className="logo-init">S</div>
                                <div className="logo-name">sesile</div>
                            </Link>
                        </div>
                    </div>
                    <div className="medium-5 cell">
                        <input type="search" name="sesile-search" className="sesile-search" id="" placeholder="Rechercher un classeur" />
                    </div>
                    <div className="medium-2 cell"></div>
                    <div className="medium-1 cell">
                        <div className="ico-notif-classeur">
                            <span className="badge">5</span>
                        </div>
                    </div>
                    <div className="medium-1 cell">
                        <div className="ico-sign-classeur">
                            <span>2</span>
                        </div>
                    </div>
                    <div className="medium-2 cell">
                        <Login/>
                    </div>
                </div>
            </div>
            <div className="cell medium-auto medium-cell-block-container">
                <div className="grid-x height100">
                    <div className="cell medium-1 medium-cell-block-y menu-left height100">
                        <div className="grid-y grid-padding-y height100 align-middle">
                            <div className="cell auto">
                                <a href="#"><span className="fa ico-hamburger"></span></a>
                            </div>
                            <div className="cell auto">
                                <Link to={"/classeur/list"}><span className="ico-new-classeur"></span></Link>
                            </div>
                            <div className="cell auto">
                                <Link to={"/classeur/list"}><span className="ico-sign-classeur"></span></Link>
                            </div>
                            <div className="cell auto">
                                <a href="#"><span className="ico-valid-classeur"></span></a>
                            </div>
                            <div className="cell auto">
                                <a href="#"><span className="ico-revert-classeur"></span></a>
                            </div>
                            <div className="cell auto">
                                <a href="#"><span className="ico-refuse-classeur"></span></a>
                            </div>
                            <div className="cell auto">
                                <a href="#"><span className="ico-notif"></span></a>
                            </div>
                            <div className="cell auto">
                                <a href="#"><span className="ico-help"></span></a>
                            </div>
                        </div>
                    </div>
                    <div className="cell medium-11 medium-cell-block-y main">
                        <div className="grid-x grid-padding-x">
                            <div className="cell medium-12 actu align-center-middle text-center">
                                <h1>Les dernières nouvelles</h1>
                                <h2>Mise à jour du 12/07/2017</h2>
                                <div className="cell medium-6">
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium adipisci distinctio dolorem dolorum illo incidunt ipsa libero mollitia, numquam officia, optio provident quaerat rerum tempore, veniam? Adipisci itaque iure maxime.</p>
                                </div>
                            </div>
                            <div className="cell medium-12">
                                <AppRoute />
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            {/*<div className="cell shrink footer">
                <h3>Here's my footer v2</h3>
            </div>*/}
        </div>
        )
    }
}

export default App;