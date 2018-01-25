import React, { Component } from 'react'
import { NavLink, Link } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import AppInfos from './AppInfos'

class Menu extends Component {
    
    static contextTypes = {
        t: func
    }

    render() {
        const { t } = this.context
        return (
            <div className="cell medium-1 medium-cell-block-y menu-left grid-y" role="navigation">

                <NavLink to="/classeur/nouveau" className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-pencil-square-o"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.new_classeur') }</div>
                    </div>
                </NavLink>
                <NavLink to="/classeurs/liste" className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-th-list"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.list_classeur') }</div>
                    </div>
                </NavLink>
                <NavLink to="/classeurs/valides" className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-check-square-o"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.validate_classeur') }</div>
                    </div>
                </NavLink>
                <NavLink to={"/classeurs/retractables"} className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-repeat"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.retractable_classeur') }</div>
                    </div>
                </NavLink>
                <NavLink to={"/classeurs/supprimes"} className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-close"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.deletable_classeur') }</div>
                    </div>
                </NavLink>
                <NavLink to={"/tableau-de-bord/stats"} className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-pie-chart"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.stats') }</div>
                    </div>
                </NavLink>
                <NavLink to={"/documentations"} className="cell auto grid-y">
                    <div className="grid-x text-center cell medium-6 align-bottom">
                        <i className="cell medium-12 fa fa-3x fa-question-circle-o"></i>
                    </div>
                    <div className="grid-x text-center cell medium-6">
                        <div className="cell medium-12">{ t('common.menu.help') }</div>
                    </div>
                </NavLink>
                <AppInfos />

            </div>
        )
    }
}

export default translate(['sesile'])(Menu)