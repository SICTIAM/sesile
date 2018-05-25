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
            user: {},
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
        this.fetchUser()
    }

    _addNotification = (notification) => {
        if (this._notificationSystem) {
            this._notificationSystem.addNotification(notification)
        }
    }

    fetchUser() {
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(user => {
                this.setState({user})
            })
    }

    render () {
        const { user } = this.state
        return (
            <I18nextProvider i18n={i18n}>
                <div className="off-canvas-wrapper">
                    <div className="off-canvas position-left hide-for-large grid-y" id="offCanvasLeft" data-off-canvas>
                        <Route render={routeProps => <Menu {...routeProps} user={user} />} />
                    </div>
                    <div className="off-canvas-content" data-off-canvas-content>
                        <div className="grid-x grid-y grid-frame">
                            <div className="cell header">
                                <div className="grid-x align-center-middle">
                                    <div className="cell large-2 small-6 logo text-center">
                                        <button type="button" className="button primary clear hide-for-large float-left" data-toggle="offCanvasLeft">
                                            <i className="fa fa-2x fa-bars" aria-hidden="true"></i>
                                        </button>
                                        <Link to={'/tableau-de-bord'}>
                                            <img src="/images/logo_sesile.png" />
                                        </Link>
                                    </div>
                                    <div className="cell large-4 show-for-large">
                                        { user.id &&
                                            <SearchClasseurs user={this.state.user}/>
                                        }
                                    </div>
                                    <div className="cell large-3 show-for-large"></div>
                                    <div className="cell large-3 small-6">
                                        <Login user={this.state.user}/>
                                    </div>
                                </div>
                            </div>
                            <div className="cell auto grid-y">
                                <div className="grid-x cell auto">
                                    <div className="hide-for-medium-only hide-for-small-only cell large-2 grid-y" style={{backgroundColor: '#f4f4f4'}}>
                                        <Route render={routeProps => <Menu {...routeProps} user={user} />} />
                                    </div>
                                    <div
                                        style={{paddingLeft: '2.5%', paddingRight: '2.5%'}}
                                        className="cell large-10 medium-12 small-12 cell-block-y main">
                                        {user.id && <Note/>}
                                        <div className="grid-x grid-padding-x medium-11">
                                            <div className="cell medium-12 small-12">
                                                <NotificationSystem ref={n => this._notificationSystem = n} style={this.notificationStyle} />
                                                {user.id &&
                                                    <AppRoute user={user}/>
                                                }
                                            </div>
                                        </div>
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