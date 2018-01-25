import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { I18nextProvider } from 'react-i18next'
import NotificationSystem from 'react-notification-system'
import PropTypes from 'prop-types'
import Login from './_components/Login'
import AppRoute from './_utils/AppRoute'
import i18n from './_utils/i18n'
import Validator from 'validatorjs'
import SearchClasseurs from './_components/SearchClasseurs'
import Note from './_components/Note'
import Menu from './_components/Menu'
import Moment from 'moment'
import { Route } from 'react-router'
Validator.useLang(window.localStorage.i18nextLng)
Moment.locale(window.localStorage.i18nextLng)

class App extends Component {

    constructor() {
        super()
        this.state = {
            isAuthenticated: null
        }
        this._notificationSystem = null
    }

    static childContextTypes = {
        _addNotification: PropTypes.func,
        t: PropTypes.func,
    }

    notificationStyle = {
        Title: {
            DefaultStyle: {
                textTransform: 'uppercase'
            }
        },
        NotificationItem: { 
            DefaultStyle: { 
                margin: '100px 5px 2px 1px',
                backgroundColor: '#404257',
                color: 'white',
                fontWeight: '700'
            }
        },
        Dismiss: {
            DefaultStyle: {
              backgroundColor: '#404257'
            }
        }
      }

    getChildContext() {
        return {
            _addNotification: this._addNotification,
            t: this.t
        }
    }

    componentDidMount() {
        $(document).foundation()
    }

    _addNotification = (notification) => {
        if (this._notificationSystem) {
            this._notificationSystem.addNotification(notification)
        }
    }

    render () {
        return (
            <I18nextProvider i18n={i18n}>
                <div className="grid-x grid-y medium-grid-frame grid-frame">
                    <div className="cell header medium-cell-block-container cell-block-container">
                        <div className="grid-x align-middle align-center">
                            <div className="medium-1 cell">
                                <div className="logo text-center">
                                    <Link to={'/tableau-de-bord'}>
                                        <div className="logo-init">S</div>
                                        <div className="logo-name">sesile</div>
                                    </Link>
                                </div>
                            </div>
                            <div className="medium-5 cell">
                                <SearchClasseurs/>
                            </div>
                            <div className="medium-2 cell"></div>
                            <div className="medium-2 cell text-center">
                                <i className="fa fa-2x fa-comments"></i>
                                <span className="badge">5</span>
                            </div>
                            <div className="medium-2 cell">
                                <Login/>
                            </div>
                        </div>
                    </div>
                    <div className="cell medium-auto medium-cell-block-container">
                        <div className="grid-x height100">

                            <Route component={Menu} />

                            <div className="grid-y cell medium-11 medium-cell-block-y main">
                                <Note/>
                                <div className="grid-x grid-padding-x medium-11">
                                    <div className="cell medium-12">
                                        <NotificationSystem ref={n => this._notificationSystem = n} style= {this.notificationStyle} />
                                        <AppRoute/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </I18nextProvider>
        )
    }
}

export default App