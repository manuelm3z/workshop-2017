import React, {
	Component
} from 'react';
import {
	StyleSheet,
	View,
	Text,
	TouchableHighlight
} from 'react-native';
import {
	connect
} from 'react-redux';
import {
	bindActionCreators
} from 'redux';
import {
	ActionCreators
} from '../actions';

class AppContainer extends Component {
	addRecipe() {
		this.props.addRecipe();
	}

	render() {
		return (
			<View>
				<Text style={styles.text}>I am App Container! Recipe Count: {this.props.recipeCount}</Text>
				<TouchableHighlight onPress={() => {this.addRecipe()}}>
					<Text>Add recipe</Text>
				</TouchableHighlight>
			</View>
		);
	}	
}

const styles = StyleSheet.create({
	text: {
		marginTop: 20
	}
});

function mapDispatchToProps(dispatch) {
	return bindActionCreators(ActionCreators, dispatch);
}

export default connect(state => {
	return {
		...state
	};
}, mapDispatchToProps)(AppContainer);