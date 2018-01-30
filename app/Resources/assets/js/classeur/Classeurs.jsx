import React, { Component } from 'react'
import { string, number, func }from 'prop-types'
import ClasseursButtonList from './ClasseursButtonList'
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
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
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

    validClasseurs = (classeurs) => { classeurs.map(classeur => {this.actionClasseur('sesile_classeur_classeurapi_validclasseur', classeur.id)})}
    signClasseurs = (classeurs) => {
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        window.open(Routing.generate('jnlpSignerFiles', {id: encodeURIComponent(ids)}))
    }
    revertClasseurs = (classeurs) => { classeurs.map(classeur => {this.actionClasseur('sesile_classeur_classeurapi_retractclasseur', classeur.id)})}
    refuseClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_refuseclasseur', classeur.id) })}
    removeClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_removeclasseur', classeur.id) })}
    deleteClasseurs = (classeurs) => { classeurs.map(classeur => { this.actionClasseur('sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE') })}
    actionClasseur (url, id, method = 'PUT') {
        fetch(Routing.generate(url, {id}),
            {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(this.handleErrors)
            .then(() => {
                this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.state.userId)
            })
    }

    render(){
        const { classeurs, limit, start, checkedAll } = this.state
        const { t } = this.context

        return (
            <div className="grid-x grid-margin-x grid-padding-x align-middle">
                <div className="cell medium-12 list-classeurs">
                    <div className="grid-x grid-padding-x tri-classeurs">
                        <div className="cell medium-3">
                            {t('common.classeurs.sort_label.name')}
                            <button onClick={() => this.listClasseurs('nom', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('nom', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2 title-sort">
                            {t('common.classeurs.sort_label.current_user')}
                        </div>
                        <div className="cell medium-2">
                            {t('common.classeurs.sort_label.create_date')}
                            <button onClick={() => this.listClasseurs('creation', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('creation', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2">
                            {t('common.classeurs.sort_label.limit_date')}
                            <button onClick={() => this.listClasseurs('validation', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                            <button onClick={() => this.listClasseurs('validation', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                        </div>
                        <div className="cell medium-2 title-sort">
                            {
                                (checkedAll || classeurs && classeurs.filter(classeur => classeur.checked).length > 1) &&
                                    <ClasseursButtonList classeurs={classeurs.filter(classeur => classeur.checked)}
                                                         validClasseur={this.validClasseurs}
                                                         revertClasseur={this.revertClasseurs}
                                                         refuseClasseur={this.refuseClasseurs}
                                                         removeClasseur={this.removeClasseurs}
                                                         deleteClasseur={this.deleteClasseurs}
                                                         signClasseur={this.signClasseurs}
                                    />
                            }
                        </div>
                        <div className="cell medium-1 text-center">
                            {t('common.classeurs.sort_label.select')}<br/>
                            <input value={checkedAll} onClick={() => this.checkAllClasseurs()} type="checkbox" />
                        </div>
                    </div>

                    <div id="classeurRow">
                        { classeurs ? (
                            classeurs.map(classeur =>
                                <ClasseursRow classeur={classeur}
                                              key={classeur.id}
                                              checkClasseur={this.checkClasseur}
                                              validClasseur={this.validClasseurs}
                                              revertClasseur={this.revertClasseurs}
                                              refuseClasseur={this.refuseClasseurs}
                                              removeClasseur={this.removeClasseurs}
                                              deleteClasseur={this.deleteClasseurs}
                                              signClasseur={this.signClasseurs}
                                />
                            )
                        ) : (<div>{ t('common.loading') }</div>)
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

export default translate(['sesile'])(Classeurs)