import React, { Component } from 'react'
import { object, array, func, number, string }from 'prop-types'
import { translate } from 'react-i18next'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        display: "list"
    }

    render () {

        const { classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur, refuseClasseur, display } = this.props

        return (

            <div className="grid-x button-list align-middle text-center">
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.validable).length &&
                        <ButtonValid classeurs={ classeurs } valid={ validClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length &&
                        <ButtonSign classeurs={ classeurs } sign={ signClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.retractable).length &&
                        <ButtonRevert classeurs={ classeurs } revert={ revertClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.refusable).length &&
                        <ButtonRefuse classeurs={ classeurs } refuseClasseur={ refuseClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.removable).length &&
                        <ButtonRemove classeurs={ classeurs } remove={ removeClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.deletable).length &&
                        <ButtonDelete classeurs={ classeurs } deleteClasseur={ deleteClasseur } display={display} />
                    }
                </div>

            </div>
        )
    }
}

ClasseursButtonList.PropTypes = {
    classeurs: array,
    validClasseur: func,
    revertClasseur: func,
    refuseClasseur: func,
    removeClasseur: func,
    deleteClasseur: func,
    signClasseur: func,
    display: string
}

export default translate(['sesile'])(ClasseursButtonList)

const ButtonValid = ({classeurs, valid, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="fa fa-check"></a>
            : <button onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="fa fa-check success button hollow"></button>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonSign = ({classeurs, sign, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => sign(classeurs)} title={ t('common.classeurs.button.sign_title') } className="fa fa-pencil"></a>
            : <button onClick={() => sign(classeurs)} title={ t('common.classeurs.button.sign_title') } className="fa fa-pencil success button hollow"></button>
    )
}
ButtonSign.contextTypes = { t: func }
ButtonSign.propTypes = {
    classeurs: array,
    sign: func
}

const ButtonRevert = ({classeurs, revert, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat"></a>
            : <button onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat warning button hollow"></button>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => remove(classeurs)} title={ t('common.classeurs.button.remove_title') } className="fa fa-times"></a>
            : <button onClick={() => remove(classeurs)} title={ t('common.classeurs.button.remove_title') } className="fa fa-times alert button hollow"></button>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') } className="fa fa-trash"></a>
            : <button onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') } className="fa fa-trash alert button hollow"></button>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}


const ButtonRefuse = ({classeurs, refuseClasseur, display}, {t}) => {
    return(
        display === "list"
            ? <a onClick={() => refuseClasseur(classeurs)} title={ t('common.classeurs.button.refus_title') } className="fa fa-minus-circle"></a>
            : <button onClick={() => refuseClasseur(classeurs)} title={ t('common.classeurs.button.refus_title') } className="fa fa-minus-circle alert button hollow"></button>
    )
}
ButtonRefuse.contextTypes = { t: func }
ButtonRefuse.propTypes = {
    classeurs: array,
    refuseClasseur: func
}