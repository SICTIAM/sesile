import React, { Component } from 'react'
import { string, number, func }from 'prop-types'
import ClasseursRow from './ClasseursRow'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'

class Classeurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props);
        this.state = {
            classeurs: null,
            sort: "id",
            order: "DESC",
            limit: 10,
            start: 0,
            checkedAll: false,
            userId: this.props.userId
        }
    }

    handleErrors(response) {
        if (response.ok) {
            return response
        }
        throw response
    }

    componentDidMount() {
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.state.userId)
    }

    listClasseurs = (sort, order, limit, start, userId) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate(this.props.url, {sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(this.handleErrors)
            .then(response => response.json())
            .then(json => {
                this.setState({classeurs : json})
                $('#classeurRow').foundation()
            })
            .then(() => {
                let classeurs = this.state.classeurs.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                this.setState({classeurs})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    checkAllClasseurs() {
        const classeurs = this.state.classeurs
        const newCheckAll = !this.state.checkedAll
        this.setState({checkedAll: newCheckAll})
        classeurs.map(classeur => classeur.checked = newCheckAll)
        this.setState({classeurs})
    }

    checkClasseur = (event) => {
        const target = event.target
        const classeurs = this.state.classeurs
        classeurs[classeurs.findIndex(classeur => classeur.id == target.id)].checked = (target.checked)
        this.setState({classeurs})
    }

    render(){
        const { classeurs, limit, start } = this.state
        const { t } = this.context

        return (
            <div className="grid-x grid-margin-x grid-padding-x align-middle">
                <div className="cell medium-12 list-classeurs">
                    <div className="grid-x grid-padding-x tri-classeurs">
                        <div className="cell medium-2">
                            {t('common.classeurs.sort')}
                            <button onClick={() => this.listClasseurs('user.nom', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('user.nom', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-3">
                            <button onClick={() => this.listClasseurs('nom', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('nom', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2">
                            <button onClick={() => this.listClasseurs('creation', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('creation', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2">
                            <button onClick={() => this.listClasseurs('validation', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('validation', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2"></div>
                        <div className="cell medium-1 text-center">
                            <input value={this.state.checkedAll} onClick={() => this.checkAllClasseurs()} type="checkbox" />
                        </div>
                    </div>

                    <div id="classeurRow">
                        { classeurs ? (
                            classeurs.map(classeur =>
                                <ClasseursRow classeur={classeur} key={classeur.id} checkClasseur={this.checkClasseur} />
                            )
                        ) : (<div>Chargement...</div>)
                        }
                    </div>
                </div>
            </div>
        )
    }
}

Classeurs.PropTypes = {
    url: string.isRequired,
    userId: number.isRequired
}

Classeurs.contextTypes = {
    t: func
}

export default translate(['sesile'])(Classeurs)