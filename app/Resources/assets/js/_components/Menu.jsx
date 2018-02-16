import React, { Component } from 'react'
import { NavLink } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import AppInfos from './AppInfos'
import {handleErrors} from "../_utils/Utils";
import {basicNotification} from "./Notifications";

class Menu extends Component {
    
    static contextTypes = {
        t: func
    }

    state = {
        classeurs: []
    }

    componentDidMount () {
        this.fetchClasseurs()
    }

    fetchClasseurs() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_classeur_classeurapi_listall'), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(classeurs => this.setState({classeurs}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { classeurs } = this.state

        return (
            <div className="cell medium-2 menu-left grid-x" role="navigation">

                <div className="cell medium-12">
                    <div className="title-bar" data-responsive-toggle="left-menu" data-hide-for="medium">
                        <button className="menu-icon" type="button" data-toggle="left-menu"></button>
                        <div className="title-bar-title">Menu</div>
                    </div>
                    <div id="left-menu">
                        <div className="top-bar-left">
                            <ul className="dropdown menu vertical icons icon-right" data-dropdown-menu>
                                <li>
                                    <NavLink to="/classeur/nouveau" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.new_classeur') }</div>
                                        <div className="cell medium-2 small-1"><i className="fa fa-2x fa-pencil-square-o"></i></div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/liste" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.validate_classeur') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-th-list"></i>
                                            <span className="badge">{classeurs.length}</span>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/valides" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.list_classeur') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-check-square-o"></i>
                                            <span className="badge">{classeurs.filter(classeur => classeur.validable).length}</span>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/retractables" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.retractable_classeur') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-repeat"></i>
                                            <span className="badge">{classeurs.filter(classeur => classeur.retractable).length}</span>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/supprimes" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.deletable_classeur') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-close"></i>
                                            <span className="badge">{classeurs.filter(classeur => classeur.deletable).length}</span>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/tableau-de-bord/stats" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.stats') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-pie-chart"></i>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/documentations" className="grid-x align-middle">
                                        <div className="cell medium-10 small-11">{ t('common.menu.help') }</div>
                                        <div className="cell medium-2 small-1">
                                            <i className="fa fa-2x fa-question-circle-o"></i>
                                        </div>
                                    </NavLink>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <AppInfos />

            </div>
        )
    }
}

export default translate(['sesile'])(Menu)