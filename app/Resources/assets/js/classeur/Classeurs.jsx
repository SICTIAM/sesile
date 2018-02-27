import React, { Component } from 'react'
import { string, number, func }from 'prop-types'
import ClasseursButtonList from './ClasseursButtonList'
import ClasseursRow from './ClasseursRow'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import ClasseurPagination from './ClasseurPagination'

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
            limit: 15,
            start: 0,
            checkedAll: false,
            userId: this.props.userId
        }
    }

    componentDidMount() {
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.state.userId)
    }

    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.state.userId)
    }
    changePage = (start) => {
        const newstart = (start * this.state.limit)
        this.setState(prevState => prevState.start = newstart)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newstart, this.state.userId)
    }
    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.setState(prevState => prevState.start = newStart)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.state.userId)
    }
    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.setState(prevState => prevState.start = newStart)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.state.userId)
    }

    listClasseurs = (sort, order, limit, start, userId) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate(this.props.url, {sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(handleErrors)
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
            <div>
                {
                    classeurs &&
                    <ClasseurPagination limit={limit}
                                        start={start}
                                        changeLimit={this.changeLimit}
                                        changePreviousPage={this.changePreviousPage}
                                        changeNextPage={this.changeNextPage}
                                        changePage={this.changePage}
                                        url={this.props.url}
                    />
                }

                        {
                            (checkedAll || classeurs && classeurs.filter(classeur => classeur.checked).length > 1) &&
                            <div className="hide-for-large grid-x align-center-middle grid-padding-y">
                                <div className="cell medium-8">
                                    <div className="grid-x panel grid-padding-y">
                                        <div className="cell medium-12">
                                            <ClasseursButtonList classeurs={classeurs.filter(classeur => classeur.checked)}
                                                                 validClasseur={this.validClasseurs}
                                                                 revertClasseur={this.revertClasseurs}
                                                                 refuseClasseur={this.refuseClasseurs}
                                                                 removeClasseur={this.removeClasseurs}
                                                                 deleteClasseur={this.deleteClasseurs}
                                                                 signClasseur={this.signClasseurs}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        }

                <div className="grid-x grid-margin-x panel align-middle">
                    <div className="cell medium-12 list-classeurs">
                        <div className="grid-x panel-heading grid-padding-x align-middle tri-classeurs">
                            <div className="cell small-6 medium-5 large-2">
                                {t('common.classeurs.sort_label.name')}
                                <button onClick={() => this.listClasseurs('nom', 'ASC', limit, start)} className="button arrow-down show-for-large" type="button">&nbsp;</button>
                                <button onClick={() => this.listClasseurs('nom', 'DESC', limit, start)} className="button arrow-up show-for-large" type="button">&nbsp;</button>
                            </div>
                            <div className="cell large-2 text-center show-for-large">
                                {t('common.classeurs.sort_label.current_user')}
                            </div>
                            <div className="cell large-2 text-center show-for-large">
                                {t('common.classeurs.sort_label.status')}
                                <button onClick={() => this.listClasseurs('status', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                                <button onClick={() => this.listClasseurs('status', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                            </div>
                            <div className="cell large-1 text-center show-for-large">
                                {t('common.classeurs.sort_label.type')}
                                <button onClick={() => this.listClasseurs('type', 'ASC', limit, start)} className="button arrow-down" type="button">&nbsp;</button>
                                <button onClick={() => this.listClasseurs('type', 'DESC', limit, start)} className="button arrow-up" type="button">&nbsp;</button>
                            </div>
                            <div className="cell small-6 medium-5 large-2 text-center">
                                {t('common.classeurs.sort_label.limit_date')}
                                <button onClick={() => this.listClasseurs('validation', 'ASC', limit, start)} className="button arrow-down show-for-large" type="button">&nbsp;</button>
                                <button onClick={() => this.listClasseurs('validation', 'DESC', limit, start)} className="button arrow-up show-for-large" type="button">&nbsp;</button>
                            </div>
                            <div className="cell large-2 title-sort show-for-large">
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
                            <div className="cell medium-2 large-1 show-for-medium text-center">
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
            </div>
        )
    }
}

Classeurs.PropTypes = {
    url: string.isRequired,
    userId: number.isRequired
}

export default translate(['sesile'])(Classeurs)