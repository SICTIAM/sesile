import React, { Component } from 'react'
import { NavLink } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import AppInfos from './AppInfos'

class Menu extends Component {
    
    static contextTypes = {
        t: func
    }

    state = {
        classeurs: []
    }

    render() {
        const { t } = this.context
        const { user } = this.props

        return (
            <div
                className="menu-left cell medium-12 grid-y"
                role="navigation"
                style={{marginTop: '0.5em', marginLeft: '0.5em', height: 'calc(100% - 1em)', backgroundColor: 'white', boxShadow: '0 2px 2px 0 rgba(34,36,38,.15)'}}>

                <div className="medium-auto" >
                    <div id="left-menu">
                        <div className="top-bar-left" style={{height:"100%"}}>
                            { user.id &&
                                <ul className="dropdown menu vertical icons icon-right" data-dropdown-menu>
                                <li>
                                    <NavLink to="/classeur/nouveau" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.new_classeur')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-pencil-square-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/liste" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.list_classeur')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-th-list"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/valides" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.validate_classeur')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-check-square-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/retractables" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.retractable_classeur')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-repeat"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/classeurs/supprimes" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.deletable_classeur')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-close"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/tableau-de-bord/stats" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.stats')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-pie-chart"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/documentations" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.menu.help')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-question-circle-o"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                            </ul>
                            }
                        </div>
                    </div>
                </div>
                <AppInfos />
            </div>
        )
    }
}

export default translate(['sesile'])(Menu)