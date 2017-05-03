import React, {
	Component
} from 'react';
import {
	ScrollView,
	View,
	Text,
	TextInput,
	Image,
	TouchableHighlight,
	StyleSheet
} from 'react-native';
import {
	connect
} from 'react-redux';

class HomeContainer extends Component {
	searchPressed() {
		this.props.fetchRecipes('bacon,cucumber,banana');
	}

	render() {
		return (
			<View style={styles.page}>
				<View>
					<TouchableHighlight onPress={() => this.searchPressed()}>
						<Text>Fetch Recipes</Text>
					</TouchableHighlight>
				</View>
				<ScrollView></ScrollView>
			</View>
		);
	}
}

const styles = StyleSheet.create({
	page: {
		marginTop: 20
	}
});

function mapStateToProps(state) {
	return {
		searchedRecipes: state.searchedRecipes
	};
}

export default connect(mapStateToProps)(HomeContainer);