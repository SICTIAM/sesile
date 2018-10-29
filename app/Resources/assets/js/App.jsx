import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { I18nextProvider } from 'react-i18next'
import NotificationSystem from 'react-notification-system'
import PropTypes from 'prop-types'
import Login from './_components/Login'
import SwitchCollectivite from './_components/SwitchCollectivite'
import AppRoute from './_utils/AppRoute'
import i18n from './_utils/i18n'
import Validator from 'validatorjs'
import SearchClasseurs from './_components/SearchClasseurs'
import Note from './_components/Note'
import Menu from './_components/Menu'
import MenuAdmin from './_components/MenuAdmin'
import Moment from 'moment'
import { Route } from 'react-router'
import SelectCollectivite from './SelectCollectivite'
import History from './_utils/History'
import {handleErrors} from "./_utils/Utils"
Validator.useLang(window.localStorage.i18nextLng)
Moment.locale(window.localStorage.i18nextLng || 'fr')

class App extends Component {

    constructor() {
        super()
        this.state = {
            user: {
                id: null
            },
            isAuthenticated: null,
            mainDomain: {
                main: false,
                mainDomain: '',
                currentDomain: ''
            },
            collectivitedomain:'',
            collectivitemessage:'',
            displaySelectCollectivite: false,
            noteObject: {
                note: {
                    title: '',
                    subtitle: '',
                    message: ''
                },
                alreadyOpen: true
            }
        }
        this._notificationSystem = null
    }

    static childContextTypes = {
        _addNotification: PropTypes.func,
        fetchUserNote: PropTypes.func,
        user: PropTypes.object,
        t: PropTypes.func,
    }

    createMarkup(message) {
        return {__html: message};
    }

    getChildContext() {
        return {
            _addNotification: this._addNotification,
            fetchUserNote: this.fetchUserNote,
            user: this.state.user,
            t: this.t
        }
    }

    componentDidMount() {
        $(document).foundation()
        this.setState({collectivitedomain: window.location.host.substr(0, window.location.host.indexOf('.'))})
        this.fetchUser()
        this.mainDomainControll()
    }

    componentDidUpdate() {
        if (this.state.collectivitedomain !== '' && this.state.collectivitemessage === '' && (this.state.mainDomain.main === false && this.state.mainDomain.mainDomain !== '') && this.state.user.id === null) {
            this.fetchMessage()
        }

    }

    _addNotification = (notification) => {
        if (this._notificationSystem) {
            this._notificationSystem.addNotification(notification)
        }
    }
    fetchMessage = () => {
        fetch(Routing.generate("sesile_main_collectiviteapi_getorganisationmessage", {domain: this.state.collectivitedomain}))
            .then(response => response.json())
            .then(json => {
                json.message && this.setState({collectivitemessage: json.message})
                json.code && json.code === 404 ? this.setState({displaySelectCollectivite: true}) : this.setState({displaySelectCollectivite: false})
            })
    }
    fetchUser = () => {
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(user => {
                if(user.length === 0) this.setState({user: {id: null}})
                else if (user.id) this.setState({user})
            })
    }
    mainDomainControll = () => {
        fetch(Routing.generate("sesile_main_default_maindomain"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(mainDomain => {
                this.setState({mainDomain})
            })
    }
    fetchUserNote = () => {
        fetch(Routing.generate('sesile_user_noteapi_getlast'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(noteObject => {if(noteObject.note) this.setState({noteObject})})
    }
    handleChangeStateUserNote = () => {
        const { noteObject } = this.state
        noteObject['alreadyOpen'] = true
        this.setState({noteObject})
    }
    handleClickConnection = () => {
        let host = window.location.host;
        let protocol = window.location.protocol;
        const redirectLoginUrl = `${protocol}//${host}/connect/ozwillo`
        this.state.mainDomain.main ?
            this.setState({displaySelectCollectivite: true}) :
            location = redirectLoginUrl
    }
    isAdminMenu = () => {
        return window.location.pathname.search("admin") === 1
    }
    render () {
        const { user } = this.state
        return (
            <I18nextProvider i18n={i18n}>
                {this.state.displaySelectCollectivite ?
                    <SelectCollectivite /> :
                    <div className="off-canvas-wrapper">
                        <div className="off-canvas position-left hide-for-large grid-y" id="offCanvasLeft" data-off-canvas>
                            {this.isAdminMenu() ?
                                <Route render={routeProps => <MenuAdmin {...routeProps} user={user}/>}/> :
                                <Route render={routeProps => <Menu {...routeProps} user={user}/>}/>
                            }
                        </div>
                        <div className="off-canvas-content" data-off-canvas-content>
                            <div className="grid-x grid-y grid-frame">
                                <div className="cell header" style={{background: 'white', borderBottom: this.isAdminMenu() ? '#d63284 solid 2px' : '#3299CC solid 2px'}}>
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
                                            {user.id &&
                                                <SearchClasseurs
                                                    user={this.state.user}
                                                    color={this.isAdminMenu() ? '#d63284' : '#3299CC'}
                                                />}
                                        </div>
                                        {user.id &&
                                            <div className="cell large-3 show-for-large">
                                                <SwitchCollectivite
                                                    color={this.isAdminMenu() ? '#d63284' : '#3299CC'}
                                                    user={user}/>
                                            </div>}
                                        <div className={`cell large-3 small-6`}>
                                            <Login
                                                handleClickConnection={this.handleClickConnection}
                                                user={this.state.user}
                                                color={this.isAdminMenu() ? '#d63284' : '#3299CC'}
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className="cell auto grid-y">
                                    <div className="grid-x cell auto">
                                        <div className="hide-for-medium-only hide-for-small-only cell large-2 grid-y" style={{backgroundColor: '#f4f4f4', width: '15%'}}>
                                            {this.isAdminMenu() ?
                                                <Route render={routeProps => <MenuAdmin {...routeProps} user={user}/>}/> :
                                                <Route render={routeProps => <Menu {...routeProps} user={user}/>}/>
                                            }
                                        </div>
                                        <div
                                            style={{paddingLeft: '2.5%', paddingRight: '2.5%'}}
                                            className="cell large-10 medium-12 small-12 cell-block-y main">
                                            {user.id && <Note noteObject={this.state.noteObject} fetchUserNote={this.fetchUserNote} handleChange={this.handleChangeStateUserNote}/>}
                                            <div className="grid-x grid-padding-x medium-11">
                                                <div className="cell medium-12 small-12">
                                                    <NotificationSystem ref={n => this._notificationSystem = n} />
                                                    {!user.id && this.state.collectivitemessage &&
                                                    <div className="grid-x" style={{marginTop: "2em"}}>
                                                        <div className="cell medium-12">
                                                            <div
                                                                className="grid-x grid-margin-x grid-padding-x align-top align-center">
                                                                <div className="grid-x grid-padding-x panel" style={{padding:"1em"}}>
                                                                    <div dangerouslySetInnerHTML={this.createMarkup(this.state.collectivitemessage)} />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    }
                                                    {user.id &&
                                                        <AppRoute user={user} updateUserInfos={this.fetchUser}/>}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>}
            </I18nextProvider>
        )
    }
}

export default App