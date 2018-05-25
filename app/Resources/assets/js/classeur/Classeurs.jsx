import React, { Component } from 'react'
import { string, number, func }from 'prop-types'
import { translate } from 'react-i18next'

import { basicNotification } from '../_components/Notifications'
import { Select } from '../_components/Form'

import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { refusClasseur, actionClasseur } from '../_utils/Classeur'

import ClasseursRow from './ClasseursRow'
import ClasseurPagination from './ClasseurPagination'
import ClasseursButtonList from './ClasseursButtonList'

class Classeurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        classeurs: [],
        sort: "id",
        order: "DESC",
        limit: 15,
        start: 0,
        checkedAll: false,
        userId: this.props.userId
    }
    componentDidMount() {
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.state.userId)
    }
    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.state.userId)
    }
    changePage = (start) => {
        const newStart = (start * this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.state.userId)
    }
    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.state.userId)
    }
    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.state.userId)
    }
    listClasseurs = (sort, order, limit, start, userId) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate(this.props.url, {orgId: this.props.user.current_org_id, sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                this.setState({classeurs})
                $('#classeurRow').foundation()
            })
            .then(this.setState({start}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                error.statusText)))
    }
    checkAllClasseurs() {
        const newCheckAll = !this.state.checkedAll
        this.setState({checkedAll: newCheckAll})
        this.setState(prevState => prevState.classeurs.map(classeur => classeur.checked = newCheckAll))
    }
    checkClasseur = (event) => {
        const target = event.target
        this.setState(prevState => {
            const IndexOfClasseurInArray =
                prevState.classeurs.findIndex(classeur => classeur.id == target.id)
            const classeur = prevState.classeurs[IndexOfClasseurInArray]
            return classeur.checked = !classeur.checked
        })
    }
    validClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_validclasseur', classeur.id, 'PUT', 'list')})}
    signClasseurs = (classeurs) => {
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        History.push('/classeurs/previsualisation', {classeurs, user: this.props.user})
    }
    revertClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_retractclasseur', classeur.id, 'PUT', 'list')})}
    refuseClasseurs = (classeurs, motif) => { classeurs.map(classeur => { refusClasseur(this, 'sesile_classeur_classeurapi_refuseclasseur', classeur.id, motif, 'list') })}
    removeClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_removeclasseur', classeur.id, 'PUT', 'list') })}
    deleteClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE', 'list') })}

    render(){
        const { classeurs, limit, start, checkedAll } = this.state
        const { t } = this.context
        const limits = [15,30,50,100]
        const classeurRowList = this.state.classeurs.map(classeur =>
                <ClasseursRow
                    classeur={classeur}
                    key={classeur.id}
                    checkClasseur={this.checkClasseur}
                    validClasseur={this.validClasseurs}
                    revertClasseur={this.revertClasseurs}
                    refuseClasseur={this.refuseClasseurs}
                    removeClasseur={this.removeClasseurs}
                    deleteClasseur={this.deleteClasseurs}
                    signClasseur={this.signClasseurs}
                    user={this.props.user}/>)
        const listLimit = limits.map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)
        return (
            <div className="cell medium-12 head-list-classeurs">
                {(checkedAll || classeurs && classeurs.filter(classeur => classeur.checked).length > 1) &&
                    <div className="hide-for-large grid-x align-center-middle grid-padding-y">
                        <div className="cell medium-8">
                            <div className="grid-x panel grid-padding-y">
                                <div className="cell medium-12 classeur-button-list">
                                    <ClasseursButtonList
                                        classeurs={classeurs.filter(classeur => classeur.checked)}
                                        validClasseur={this.validClasseurs}
                                        revertClasseur={this.revertClasseurs}
                                        refuseClasseur={this.refuseClasseurs}
                                        removeClasseur={this.removeClasseurs}
                                        deleteClasseur={this.deleteClasseurs}
                                        signClasseur={this.signClasseurs}
                                        id={"button-lists-small"}
                                        user={this.props.user}/>
                                </div>
                            </div>
                        </div>
                    </div>}
                <div className="grid-x panel align-middle">
                    <div className="cell medium-12 list-classeurs">
                        <div className="grid-x panel-heading align-middle text-center tri-classeurs">
                            <div
                                style={{paddingLeft: '5px'}}
                                className="cell small-6 medium-6 large-2 text-left large-offset-1">
                                {t('common.classeurs.sort_label.name')}
                            </div>
                            <div className="cell large-2 show-for-large">
                                {t('common.classeurs.sort_label.current_user')}
                            </div>
                            <div className="cell small-6 medium-5 large-2">
                                {t('common.classeurs.sort_label.limit_date')}
                            </div>
                            <div className="cell large-2 show-for-large">
                                {t('common.classeurs.sort_label.type')}
                            </div>
                            <div className="cell large-2 title-sort show-for-large">
                                {(checkedAll || classeurs && classeurs.filter(classeur => classeur.checked).length > 1) &&
                                    <ClasseursButtonList
                                        classeurs={classeurs.filter(classeur => classeur.checked)}
                                        validClasseur={this.validClasseurs}
                                        revertClasseur={this.revertClasseurs}
                                        refuseClasseur={this.refuseClasseurs}
                                        removeClasseur={this.removeClasseurs}
                                        deleteClasseur={this.deleteClasseurs}
                                        signClasseur={this.signClasseurs}
                                        id={"button-lists-large"}
                                        user={this.props.user}/>}
                            </div>
                            <div className="cell medium-1 large-1 show-for-medium">
                                <input value={checkedAll} onClick={() => this.checkAllClasseurs()} type="checkbox" />
                            </div>
                        </div>
                        <div id="classeurRow">
                            {classeurs.length > 0 ?
                                classeurRowList :
                                <div className="grid-x panel-body align-middle align-center" style={{minHeight: '5em'}}>
                                    {t('common.loading')}
                                </div>}
                        </div>
                        {classeurs &&
                            <div
                                className="grid-x panel-heading"
                                style={{borderTop: 'solid 1px #a7a7a7', padding: '10px'}}>
                                <Select
                                    id="limit"
                                    style={{margin: 0, width: '70px', height: '2.3em'}}
                                    value={this.state.limit}
                                    className="cell small-6 medium-1 large-1"
                                    onChange={this.changeLimit}
                                    children={listLimit}/>
                                <div
                                    className="cell small-6 medium-3 large-3 medium-offset-8 large-offset-8"
                                    style={{height: '2.3em'}}>
                                        <ClasseurPagination
                                            limit={limit}
                                            start={start}
                                            changeLimit={this.changeLimit}
                                            changePreviousPage={this.changePreviousPage}
                                            changeNextPage={this.changeNextPage}
                                            changePage={this.changePage}
                                            url={this.props.url}/>
                                </div>
                            </div>}
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