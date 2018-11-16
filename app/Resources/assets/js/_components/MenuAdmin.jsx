import React, { Component } from 'react'
import { NavLink } from 'react-router-dom'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import AppInfos from './AppInfos'

class MenuAdmin extends Component {

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

                <div className="medium-auto">
                    <div id="left-menu">
                        <div className="top-bar-left" style={{height:"100%"}}>
                            { user.id &&
                            <ul className="dropdown menu vertical icons icon-right" data-dropdown-menu>
                                <li>
                                    <NavLink to="/admin/tableau-de-bord" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.collectivite.name')}
                                                        </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-building"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/utilisateurs" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.user.name', {count: 2})}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-user"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/circuits-de-validation" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.circuit.name', {count: 2})}
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
                                    <NavLink to="/admin/groupes" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.group.name', {count: 2})}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-users"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/types-classeur" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.type.name', {count: 2})}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-tags"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/collectivites" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.collectivite.name', {count: 2})}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-city"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/documentations" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('common.help_board.title')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-file"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/emailing" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.emailing.name')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-paper-plane"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/notes" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.notes.name', {context: 'plural'})}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-clipboard"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/admin/migration" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.migration')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-cloud-upload"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </NavLink>
                                </li>
                                <li>
                                    <NavLink to="/tableau-de-bord" className="grid-x align-middle">
                                        <div className="cell small-12">
                                            <div className="grid-x text-center large-text-left align-middle">
                                                <div className="cell small-12 small-order-2 large-10 large-order-1">
                                                    <span className="text-bold">
                                                        {t('admin.backuser')}
                                                    </span>
                                                </div>
                                                <div className="cell small-12 small-order-1 large-2 large-order-2">
                                                    <i className="fa fa-reply"></i>
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

export default translate(['sesile'])(MenuAdmin)